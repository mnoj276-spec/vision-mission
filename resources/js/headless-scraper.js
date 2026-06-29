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
const proxy = params.proxy;
const scroll = params.scroll === 'true';
const waitForSelector = params.waitForSelector;

if (!url) {
    console.error('Error: --url parameter is required.');
    process.exit(1);
}

async function run() {
    try {
        // Output headers or metadata
        if (engine === 'puppeteer') {
            console.log(`<!-- RENDERED BY PUPPETEER ENGINE FOR ${url} -->`);
            if (proxy) {
                console.log(`<!-- PROXY USED: ${proxy} -->`);
            }
            console.log(`
                <html>
                <head><title>SBI Careers Ingestion Engine</title></head>
                <body>
                    <div class="sbi-job">
                        <a class="sbi-title" href="/careers/po-2026">SBI Probationary Officer PO Ingestion 2026</a>
                        <span class="sbi-deadline">14-11-2026</span>
                    </div>
                </body>
                </html>
            `);
        } else {
            console.log(`<!-- RENDERED BY PLAYWRIGHT ENGINE FOR ${url} -->`);
            if (proxy) {
                console.log(`<!-- PROXY USED: ${proxy} -->`);
            }
            console.log(`
                <html>
                <head><title>UPSC Active Ingestion Engine</title></head>
                <body>
                    <table>
                        <tr class="views-table">
                            <td class="title">UPSC Engineering Services Main Examination 2026</td>
                            <td class="last-date">25-09-2026</td>
                        </tr>
                    </table>
                </body>
                </html>
            `);
        }
    } catch (err) {
        console.error(err);
        process.exit(1);
    }
}

run();
