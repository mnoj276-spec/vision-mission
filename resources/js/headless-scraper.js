import process from 'process';

// Parse command line arguments
const args = process.argv.slice(2);
const params = {};
for (let i = 0; i < args.length; i += 2) {
    const key = args[i].replace(/^--/, '');
    const val = args[i + 1];
    params[key] = val;
}

const url = params.url;
const engine = params.engine || 'puppeteer';

if (!url) {
    console.error('Error: --url parameter is required.');
    process.exit(1);
}

async function run() {
    try {
        console.log(`<!-- RENDERED BY ${engine.toUpperCase()} ENGINE FOR ${url} -->`);
        console.log(`
            <html>
            <head><title>Headless Engine Disabled</title></head>
            <body>
                <div class="error-message">
                    ERROR: The headless browser scraper mock has been disabled for security reasons to prevent the generation of fake government jobs. Real Playwright/Puppeteer implementation is required.
                </div>
            </body>
            </html>
        `);
    } catch (err) {
        console.error(err);
        process.exit(1);
    }
}

run();
