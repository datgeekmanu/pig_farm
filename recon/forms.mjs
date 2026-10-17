import { chromium } from 'playwright';
import fs from 'fs';

const EMAIL = 'yipakek586@iconcubic.com';
const PASS = 'mowgliagrandi';
const OUT = './shots/forms';
fs.mkdirSync(OUT, { recursive: true });

// each target: name, route, then a list of button/link/card texts to click in order to reach a form
const TARGETS = [
  ['lot-create',        '/app/mes-porcs',    ['Lots', 'Ajouter', 'Nouveau', 'Créer', 'Nouveau lot', 'Ajouter un lot']],
  ['pig-create',        '/app/mes-porcs',    ['Suivi Individuel', 'Ajouter', 'Nouveau', 'Ajouter un porc']],
  ['mortality-create',  '/app/mes-porcs',    ['Mortalité', 'Ajouter', 'Déclarer', 'Nouveau']],
  ['repro-gestation',   '/app/reproduction', ['Gestation', 'Ajouter', 'Saillie', 'Nouvelle saillie', 'Nouveau']],
  ['repro-maternite',   '/app/reproduction', ['Maternité', 'Ajouter', 'Nouvelle', 'Nouveau', 'Mise bas']],
  ['repro-fiches',      '/app/reproduction', ['Fiches reproducteurs', 'Ajouter', 'Nouveau', 'Nouvelle']],
  ['repro-suivi-indiv', '/app/reproduction', ['Suivi Individuel', 'Ajouter', 'Nouveau']],
  ['form-manuel',       '/app/formulation',  ['Mode Manuel']],
  ['form-economique',   '/app/formulation',  ['Économique']],
  ['form-analyse',      '/app/formulation',  ['Analyser']],
  ['stock-alim',        '/app/stocks',       ['🌾 Alimentation', 'Ajouter']],
  ['stock-vet',         '/app/stocks',       ['💊 Vétérinaire', 'Ajouter']],
  ['treat-lot',         '/app/treatments',   ['Lots', 'Ajouter traitement lot']],
  ['treat-indiv',       '/app/treatments',   ['Individuels', 'Ajouter']],
  ['treat-prophy',      '/app/treatments',   ['Prophylaxie', 'Ajouter', 'Nouveau']],
  ['salary-create',     '/app/salaries',     ['Ajouter']],
  ['sale-create',       '/app/sales',        ['Nouvelle vente']],
  ['expense-create',    '/app/expenses',     ['Ajouter une dépense']],
  ['team-create',       '/app/settings/team',['Ajouter un membre']],
];

const dumpForm = async (p) => {
  // prefer dialog scope
  const scope = (await p.$('[role=dialog]')) ? '[role=dialog]' : 'body';
  const fields = await p.$$eval(`${scope} input, ${scope} select, ${scope} textarea`, els => els.map(e => {
    let label = e.getAttribute('aria-label') || '';
    if (!label && e.id) { const l = document.querySelector(`label[for="${e.id}"]`); if (l) label = l.innerText.trim(); }
    if (!label) { const l = e.closest('label'); if (l) label = l.innerText.trim(); }
    if (!label) { // previous sibling text
      let prev = e.previousElementSibling;
      if (prev && ['LABEL','SPAN','P','DIV'].includes(prev.tagName)) label = (prev.innerText||'').trim().slice(0,60);
    }
    return { t: e.tagName.toLowerCase(), type: e.type||'', name: e.name||'', ph: e.placeholder||'', label,
             opts: e.tagName==='SELECT' ? [...e.options].map(o=>o.text.trim()).slice(0,25) : undefined };
  }));
  const dialogTitle = await p.$$eval(`${scope} h1,${scope} h2,${scope} h3`, e => e.map(x=>x.innerText.trim()).filter(Boolean).slice(0,6));
  const labels = await p.$$eval(`${scope} label`, e => [...new Set(e.map(x=>x.innerText.trim()).filter(Boolean))].slice(0,40));
  return { scope, dialogTitle, labels, fields };
};

const b = await chromium.launch();
const ctx = await b.newContext({ viewport: { width: 1366, height: 1000 }, locale: 'fr-FR' });
const p = await ctx.newPage();
await p.goto('https://afaporc.com/auth', { waitUntil: 'networkidle', timeout: 60000 });
await p.fill('input[type=email]', EMAIL);
await p.fill('input[type=password]', PASS);
await Promise.all([p.waitForLoadState('networkidle').catch(()=>{}), p.click('button:has-text("Se connecter")')]);
await p.waitForTimeout(3000);
console.log('logged in');

const clickText = async (txt) => {
  // try button/link/card with exact-ish text
  const locs = [
    p.getByRole('button', { name: txt, exact: false }),
    p.getByRole('link', { name: txt, exact: false }),
    p.getByText(txt, { exact: false }),
  ];
  for (const loc of locs) {
    const el = loc.first();
    if (await el.count() && await el.isVisible().catch(()=>false)) {
      await el.click({ timeout: 4000 }).catch(()=>{});
      return true;
    }
  }
  return false;
};

const report = {};
for (const [name, route, steps] of TARGETS) {
  try {
    await p.goto('https://afaporc.com' + route, { waitUntil: 'networkidle', timeout: 40000 });
    await p.waitForTimeout(1500);
    const clicked = [];
    for (const s of steps) {
      const ok = await clickText(s);
      await p.waitForTimeout(1200);
      if (ok) clicked.push(s);
      // stop early once a dialog or form with >1 field appears
      const hasDialog = await p.$('[role=dialog]');
      const nFields = await p.$$eval('input,select,textarea', e=>e.length);
      if (hasDialog && nFields > 1) break;
    }
    const dump = await dumpForm(p);
    await p.screenshot({ path: `${OUT}/${name}.png`, fullPage: true });
    report[name] = { route, clicked, url: p.url(), ...dump };
    console.log(`\n==== ${name} | clicked:[${clicked.join(' > ')}] | url:${p.url().replace('https://afaporc.com','')}`);
    console.log('TITLE:', JSON.stringify(dump.dialogTitle));
    console.log('LABELS:', JSON.stringify(dump.labels));
    console.log('FIELDS:', JSON.stringify(dump.fields));
    // close any modal
    await p.keyboard.press('Escape').catch(()=>{});
    await p.waitForTimeout(400);
  } catch (e) {
    console.log(`\n==== ${name} ERR ${e.message}`);
    report[name] = { route, error: e.message };
  }
}
fs.writeFileSync(`${OUT}/forms-report.json`, JSON.stringify(report, null, 2));
await b.close();
console.log('\nDONE -> shots/forms/forms-report.json  (NO data submitted)');
