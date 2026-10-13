const { chromium } = require('playwright');
const assert = require('node:assert/strict');
(async () => {
 const browser = await chromium.launch({headless:true,executablePath:process.env.CHROMIUM_BINARY||chromium.executablePath(),args:['--no-sandbox']});
 const page = await browser.newPage({viewport:{width:1440,height:1000}});
 const errors=[];page.on('pageerror',e=>errors.push(e.message));
 await page.route('**/*',route=>new URL(route.request().url()).hostname==='127.0.0.1'?route.continue():route.abort());
 const base=process.env.BASE_URL;await page.goto(base+'/index.php?route=/login');await page.getByLabel('Username',{exact:true}).fill('admin');await page.getByLabel('Password',{exact:true}).fill('Correct-password-123');await page.getByRole('button',{name:'Sign in',exact:true}).click();await page.getByRole('link',{name:'Example Customer',exact:true}).first().click();
 await page.screenshot({path:process.env.SCREENSHOT_DIR+'/dashboard.png',fullPage:true});
 await page.getByRole('link',{name:'View all',exact:true}).nth(1).click();await page.getByRole('link',{name:'+ New document'}).click();await page.getByLabel('Title',{exact:true}).fill('Browser-created procedure');const editor=page.getByRole('textbox',{name:'Article content',exact:true});await editor.fill('Documented using the rich-text editor.');await page.getByRole('button',{name:'Save document',exact:true}).click();await page.getByRole('heading',{name:'Browser-created procedure',exact:true}).waitFor();assert(await page.locator('.article-content').textContent()==='Documented using the rich-text editor.');
 await page.screenshot({path:process.env.SCREENSHOT_DIR+'/article.png',fullPage:true});
 await page.setViewportSize({width:390,height:844});await page.screenshot({path:process.env.SCREENSHOT_DIR+'/mobile.png',fullPage:true});assert(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'Mobile page overflows horizontally');
 await page.getByLabel('Search documentation',{exact:true}).fill('Browser-created');await page.getByRole('button',{name:'Search',exact:true}).first().click();await page.getByRole('link',{name:'Browser-created procedure',exact:true}).waitFor();assert.equal(errors.length,0,errors.join('\n'));
 await browser.close();console.log('Browser acceptance passed: login, navigation, rich-text save, search, desktop/mobile, no external UI assets.');
})().catch(e=>{console.error(e);process.exit(1)});
