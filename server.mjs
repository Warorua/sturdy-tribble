import express from 'express';
import puppeteer from 'puppeteer';
console.log("Using Chromium from:", puppeteer.executablePath());

const app = express();
const PORT = process.env.PORT || 3000; // THIS IS REQUIRED FOR RENDER

app.get(['/', '/run-simulate'], async (req, res) => {
    const obj = req.query.obj;

    if (!obj) {
        return res.status(400).json({ success: false, error: 'Missing obj parameter' });
    }

    try {
        const runSimulation = await import('./sac.mjs');
        const result = await runSimulation.default(obj);
        res.json({ success: true, intercepted: result });
    } catch (error) {
        console.error(error);
        res.status(500).json({ success: false, error: error.message });
    }
});

app.listen(PORT, () => {
    console.log(`✅ Server is listening on port ${PORT}`);
});
