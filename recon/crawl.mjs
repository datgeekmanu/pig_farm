import { chromium } from 'playwright';
import fs from 'fs';

const EMAIL = 'yipakek586@iconcubic.com';
const PASS = 'mowgliagrandi';
const OUT = './shots';
fs.mkdirSync(OUT, { recursive: true });

const ROUTES = [
  ['accueil', '/app'],
  ['mes-porcs', '/app/mes-porcs'],
  ['reproduction', '/app/reproduction'],
  ['formulation', '/app/formulation'],
  ['stocks', '/app/stocks'],
  ['treatments', '/app/treatments'],
  ['salaries', '/app/salaries'],
  ['sales', '/app/sales'],
  ['expenses', '/app/expenses'],
  ['finances', '/app/finances'],
  ['finances-overview', '/app/finances/overview'],
  ['settings-team', '/app/settings/team'],
  ['settings-notifications', '/app/settings/notifications'],
  ['settings-farm', '/app/settings/farm'],
  ['account', '/app/account'],
  ['referral', '/app/referral'],
  ['tutoriels', '/app/tutoriels'],
];

const b = await chromium.launch();
const ctx = await b.newContext({ viewport: { width: 1366, height: 1000 }, locale: 'fr-FR' });
const p = await ctx.newPage();

await p.goto('https://afaporc.com/auth', { waitUntil: 'networkidle', timeout: 60000 });
await p.fill('input[type=email]', EMAIL);
await p.fill('input[type=password]', PASS);
await Promise.all([p.waitForLoadState('networkidle').catch(()=>{}), p.click('button:has-text("Se connecter")')]);
await p.waitForTimeout(3000);
console.log('logged in, url=', p.url());

const report = {};
for (const [name, route] of ROUTES) {
  try {
    await p.goto('https://afaporc.com' + route, { waitUntil: 'networkidle', timeout: 45000 });
    await p.waitForTimeout(2500);
    await p.screenshot({ path: `${OUT}/${name}.png`, fullPage: true });

    const headings = await p.$$eval('h1,h2,h3', e => [...new Set(e.map(x=>x.innerText.trim()).filter(Boolean))]);
    const buttons = await p.$$eval('button', e => [...new Set(e.map(x=>x.innerText.trim()).filter(Boolean))]);
    const tabs = await p.$$eval('[role=tab], .tab, nav button', e => [...new Set(e.map(x=>x.innerText.trim()).filter(Boolean))]);
    const tableHeaders = await p.$$eval('table th', e => e.map(x=>x.innerText.trim()).filter(Boolean));
    const formFields = await p.$$eval('input,select,textarea', e => e.map(x=>({t:x.tagName.toLowerCase(),type:x.type||'',name:x.name||'',ph:x.placeholder||'',label:x.getAttribute('aria-label')||''})));
    const selects = await p.$$eval('select', e => e.map(s=>({name:s.name,opts:[...s.options].map(o=>o.text.trim()).slice(0,20)})));

    report[name] = { route, headings, buttons: buttons.slice(0,30), tabs, tableHeaders, formFields: formFields.slice(0,40), selects };
    console.log(`\n==== ${name} (${route}) ====`);
    console.log('H:', JSON.stringify(headings));
    console.log('TABS:', JSON.stringify(tabs));
    console.log('BTN:', JSON.stringify(buttons.slice(0,30)));
    console.log('TH:', JSON.stringify(tableHeaders));
    console.log('FIELDS:', JSON.stringify(formFields.slice(0,40)));
    if (selects.length) console.log('SELECTS:', JSON.stringify(selects));
  } catch (e) {
    console.log(`\n==== ${name} ERR: ${e.message}`);
    report[name] = { route, error: e.message };
  }
}
fs.writeFileSync(`${OUT}/report.json`, JSON.stringify(report, null, 2));
await b.close();
console.log('\nDONE -> shots/report.json');
