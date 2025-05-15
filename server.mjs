import express from 'express';

const app = express();
const PORT = process.env.PORT || 3000;

// Unified route for both `/` and `/run-simulate`
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
