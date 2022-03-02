"use strict";

if (process.argv.length <= 2) {
    console.log('No arguments provided!');
    return;
}

const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch();
    const page = await browser.newPage()
    await page.goto(process.argv[2], {waitUntil: 'networkidle2', timeout: 10000});
    await page.waitForSelector('.app-container-frame .build-main-container ul li:not(:empty)');
    const data = await page.content();
    console.log(data)
    await browser.close()
})()
