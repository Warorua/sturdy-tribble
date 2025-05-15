// server.mjs
import express from 'express';
import runSimulation from './sac.mjs';

const app = express();
const PORT = 3000;

app.get('/run-simulate', async (req, res) => {
    try {
        const result = await runSimulation();
        res.json({ success: true, intercepted: result });
    } catch (error) {
        console.error(error);
        res.status(500).json({ success: false, error: error.message });
    }
});

app.listen(PORT, () => {
    console.log(`Server running at http://localhost:${PORT}`);
});
