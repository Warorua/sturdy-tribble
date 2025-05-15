// sac.mjs
import puppeteer from 'puppeteer';
import fetch from 'node-fetch';
import os from 'os';
import path from 'path';

export default async function runSimulation(obj) {
    const apiUrl = `https://kotnova.com/iframev2.1.php?obj=${encodeURIComponent(obj)}`;
    const response = await fetch(apiUrl);
    const fields = await response.json();

    const browser = await puppeteer.launch({
        headless: 'new',
        executablePath: path.join(
            os.homedir(),
            '.cache/puppeteer/chrome/linux-1108766/chrome-linux64/chrome-linux64/chrome'
        ),
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-gpu',
            '--no-zygote',
            '--single-process'
        ]
    });
    
    

    const page = await browser.newPage();

    const captured = [];

    page.on('request', async request => {
        const url = request.url();
        const method = request.method();

        if (method === 'POST' && url.includes('ipn.php')) {
            const postData = request.postData();
            const parsed = new URLSearchParams(postData);
            const postFields = {};
            for (const [key, value] of parsed.entries()) {
                postFields[key] = value;
            }

            captured.push({
                url,
                method,
                fields: postFields
            });
        }
    });

    let html = '<html><body><form method="POST" id="form" action="https://secureacceptance.cybersource.com/silent/pay">';
    for (const key in fields) {
        html += `<input type="hidden" name="${key}" value="${fields[key]}">`;
    }
    html += '</form><script>document.getElementById("form").submit();</script></body></html>';

    await page.setContent(html);
    await new Promise(resolve => setTimeout(resolve, 10000)); // wait for form submission
    await browser.close();

    return captured;
}
