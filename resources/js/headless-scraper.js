import puppeteer from 'puppeteer';
import { execSync } from 'child_process';
import { existsSync } from 'fs';

// Parse command line arguments
const args = process.argv.slice(2);
let url = null;
let proxy = null;

for (let i = 0; i < args.length; i++) {
    if (args[i] === '--url') {
        url = args[i + 1];
    } else if (args[i] === '--proxy') {
        proxy = args[i + 1];
    }
}

if (!url) {
    console.error(JSON.stringify({ status: 'error', message: 'URL is required' }));
    process.exit(1);
}

/**
 * Detect the Chrome executable path across platforms.
 */
function findChromePath() {
    const candidates = [
        // Windows
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        // macOS
        '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
        // Linux
        '/usr/bin/google-chrome',
        '/usr/bin/chromium-browser',
        '/usr/bin/chromium',
    ];

    for (const p of candidates) {
        if (existsSync(p)) return p;
    }

    // Fall back to Puppeteer's bundled browser (may not exist)
    return undefined;
}

(async () => {
    let browser = null;
    try {
        const launchArgs = [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-gpu'
        ];

        if (proxy) {
            launchArgs.push(`--proxy-server=${proxy}`);
        }

        const chromePath = findChromePath();

        browser = await puppeteer.launch({
            headless: 'new',
            ...(chromePath ? { executablePath: chromePath } : {}),
            args: launchArgs,
            timeout: 15000
        });

        const page = await browser.newPage();

        // Set realistic user agent
        await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36');

        // Navigate and wait for initial DOM
        await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });

        // Allow time for SPA frameworks (Angular/React) to render
        await new Promise(resolve => setTimeout(resolve, 5000));

        // Attempt to detect when a specific selector appears (graceful)
        try {
            await Promise.any([
                page.waitForSelector('table', { timeout: 3000 }),
                page.waitForSelector('.content', { timeout: 3000 }),
                page.waitForSelector('main', { timeout: 3000 })
            ]);
        } catch (e) {
            // Graceful degradation - extract whatever is available
        }

        const html = await page.evaluate(() => document.documentElement.outerHTML);

        // Output HTML to stdout for HybridScrapingEngine consumption
        console.log(html);

        await browser.close();
        process.exit(0);

    } catch (error) {
        console.error(`Headless extraction failed: ${error.message}`);
        if (browser) {
            await browser.close();
        }
        process.exit(1);
    }
})();
