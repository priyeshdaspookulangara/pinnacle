const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch();
    const context = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await context.newPage();

    // Login
    await page.goto('http://127.0.0.1:8000/index.php?page=login');
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'Admin@123456');
    await page.click('button[type="submit"]');
    await page.waitForTimeout(1000);

    // Navigate to referrals page
    await page.goto('http://127.0.0.1:8000/index.php?page=referrals');
    await page.waitForTimeout(1000);

    await page.screenshot({ path: '/home/jules/verification/screenshots/my_referrals.png', fullPage: true });
    console.log('Screenshot saved to /home/jules/verification/screenshots/my_referrals.png');

    await browser.close();
})();
