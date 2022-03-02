"use strict";

if (process.argv.length <= 3) {
    console.log('No arguments provided!');
    return;
}

const puppeteer = require('puppeteer-core');

(async () => {
    const browser = await puppeteer.launch({executablePath: process.argv[3]});
    const page = await browser.newPage()
    await page.goto(process.argv[2], {waitUntil: 'networkidle2', timeout: 10000});
    await page.waitForSelector('.app-container-frame .build-main-container ul li:not(:empty)');
    const data = await page.content();
    console.log(data)
    await browser.close()
})()
