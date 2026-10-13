const { chromium } = require("playwright");
const { exec } = require("child_process");
const fs = require("fs");
const path = require("path");

(async () => {
    try {
        const outDir = path.join(__dirname, "..", "docs", "screenshots");
        fs.mkdirSync(outDir, { recursive: true });

        const browser = await chromium.launch({ headless: true });
        const context = await browser.newContext({
            viewport: { width: 1280, height: 800 },
        });
        const page = await context.newPage();

        console.log("Navigating to http://127.0.0.1:8000");
        await page.goto("http://127.0.0.1:8000", { waitUntil: "networkidle" });
        await page.waitForTimeout(1000);

        // Desktop theme toggle
        const toggleSelector =
            '[data-role="theme-toggle-btn"], button[onclick*="toggleTheme"]';
        await page.waitForSelector(toggleSelector, { timeout: 5000 });
        console.log("Clicking desktop theme toggle");
        await page.click(toggleSelector);
        await page.waitForTimeout(500);
        await page.screenshot({
            path: path.join(outDir, "theme-desktop.png"),
            fullPage: true,
        });
        console.log("Saved theme-desktop.png");

        // Start sample generator in background to emit realtime events
        console.log("Starting sample generator (5 events)");
        const child = exec("php artisan sample:water-quality --count=5", {
            cwd: process.cwd(),
        });
        child.stdout.on("data", (d) => process.stdout.write("[sample] " + d));
        child.stderr.on("data", (d) =>
            process.stderr.write("[sample-err] " + d)
        );

        // Wait for chart to appear
        const chartSelector =
            "[data-chart-id], .apexcharts-canvas, #water-quality-chart";
        console.log("Waiting for chart selector...");
        await page
            .waitForSelector(chartSelector, { timeout: 8000 })
            .catch(() => console.warn("Chart selector not found"));

        // Wait a bit to allow realtime events to be received and plotted
        console.log("Waiting 6s for realtime updates");
        await page.waitForTimeout(6000);
        await page.screenshot({
            path: path.join(outDir, "chart-after-events.png"),
            fullPage: true,
        });
        console.log("Saved chart-after-events.png");

        // Mobile interactions: resize and open mobile nav
        await page.setViewportSize({ width: 375, height: 812 });
        await page.waitForTimeout(300);

        // Try to click the hamburger (find visible nav button)
        const navButtons = await page.$$("nav button");
        let clicked = false;
        for (const b of navButtons) {
            try {
                const box = await b.boundingBox();
                if (box && box.width > 8 && box.height > 8) {
                    await b.click();
                    clicked = true;
                    break;
                }
            } catch (e) {}
        }
        if (!clicked)
            console.warn("Could not find hamburger button to open mobile nav");
        await page.waitForTimeout(400);

        // Mobile theme toggle inside panel
        const mobileToggle = '[data-role="theme-toggle-btn"]';
        await page
            .waitForSelector(mobileToggle, { timeout: 3000 })
            .catch(() => console.warn("Mobile theme toggle not found"));
        await page.click(mobileToggle).catch(() => {});
        await page.waitForTimeout(500);
        await page.screenshot({
            path: path.join(outDir, "theme-mobile.png"),
            fullPage: true,
        });
        console.log("Saved theme-mobile.png");

        // Kill sample child if still running
        try {
            child.kill();
        } catch (e) {}

        await browser.close();
        console.log(
            "Playwright smoke test completed. Screenshots in docs/screenshots/"
        );
        process.exit(0);
    } catch (err) {
        console.error(err);
        process.exit(1);
    }
})();
