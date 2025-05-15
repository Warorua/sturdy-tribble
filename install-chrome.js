import { exec } from 'child_process';

exec('npx puppeteer browsers install chrome', (error, stdout, stderr) => {
  if (error) {
    console.error(`Error installing Chrome: ${error.message}`);
    return;
  }
  if (stderr) {
    console.error(`stderr: ${stderr}`);
  }
  console.log(`stdout: ${stdout}`);
});
