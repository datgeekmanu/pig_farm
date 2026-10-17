import { chromium } from 'playwright';
import fs from 'fs';

const EMAIL = 'yipakek586@iconcubic.com';
const PASS = 'mowgliagrandi';
const OUT = './shots';
fs.mkdirSync(OUT, { recursive: true });

const log = (...a) => console.log(...a);

const b = await chromium.launch();
const ctx = await b.newContext({ viewport: { width: 1366, height: 900 }, locale: 'fr-FR' });
const p = await ctx.newPage();

log('goto /app');
await p.goto('https://afaporc.com/app', { waitUntil: 'networkidle', timeout: 60000 }).catch(e => log('nav warn', e.message));
await p.waitForTimeout(2000);
log('url after load:', p.url());
await p.screenshot({ path: `${OUT}/01-login.png`, fullPage: true });

// dump all inputs
const inputs = await p.$$eval('input', els => els.map(e => ({ type: e.type, name: e.name, id: e.id, ph: e.placeholder })));
log('INPUTS', JSON.stringify(inputs));
const btns = await p.$$eval('button, [type=submit], a', els => els.slice(0,40).map(e => (e.innerText||'').trim()).filter(Boolean));
log('BTNS/LINKS', JSON.stringify(btns));

// try fill login
const emailSel = 'input[type=email], input[name*=mail i], input[id*=mail i], input[placeholder*=mail i]';
const passSel = 'input[type=password]';
try {
  await p.fill(emailSel, EMAIL, { timeout: 8000 });
  await p.fill(passSel, PASS, { timeout: 8000 });
  log('filled creds');
  // submit
  const submit = await p.$('button[type=submit], [type=submit]') || await p.$('button');
  await Promise.all([
    p.waitForLoadState('networkidle', { timeout: 30000 }).catch(()=>{}),
    submit ? submit.click() : p.keyboard.press('Enter'),
  ]);
  await p.waitForTimeout(4000);
} catch (e) {
  log('login fill ERR', e.message);
}
log('url after login:', p.url());
await p.screenshot({ path: `${OUT}/02-after-login.png`, fullPage: true });

// grab nav
const nav = await p.$$eval('a[href], [role=menuitem], nav a, aside a, .sidebar a', els =>
  [...new Map(els.map(e => [(e.getAttribute('href')||'')+'|'+(e.innerText||'').trim(), { href: e.getAttribute('href'), text: (e.innerText||'').trim() }])).values()]
   .filter(x => x.text));
log('NAV', JSON.stringify(nav, null, 1));

// page body text (trimmed)
const bodyText = (await p.innerText('body').catch(()=> '')).slice(0, 3000);
log('BODYTEXT >>>', bodyText);

await b.close();
log('CLOSED');
