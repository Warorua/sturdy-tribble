// simulate-and-capture.mjs

import puppeteer from 'puppeteer';
import fetch from 'node-fetch';

const apiUrl = 'https://kotnova.com/iframev2.1.php';
const response = await fetch(apiUrl);
const fields = await response.json();

const browser = await puppeteer.launch({ headless: false, defaultViewport: null });
const page = await browser.newPage();

page.on('request', async request => {
    const url = request.url();
    const method = request.method();

    if (method === 'POST') {
        const postData = request.postData();
        if (url.includes('ipn.php')) {
            console.log('\n🔥 POST to IPN.PHP Detected');
            console.log('URL:', url);
            console.log('Method:', method);
            const parsed = new URLSearchParams(postData);
            for (const [key, value] of parsed.entries()) {
                console.log(`${key}\t${value}`);
            }
        }
    }
});

let html = '<html><body><form method="POST" id="form" action="https://secureacceptance.cybersource.com/silent/pay">';
for (const key in fields) {
    html += `<input type="hidden" name="${key}" value="${fields[key]}">`;
}
html += '</form><script>document.getElementById("form").submit();</script></body></html>';

await page.setContent(html);
console.log('🚀 Form submitted to Cybersource. Waiting for callback...');
await new Promise(resolve => setTimeout(resolve, 20000));
await browser.close();
