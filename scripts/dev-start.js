#!/usr/bin/env node
import { spawn } from "node:child_process";
import path from "path";
import { fileURLToPath } from "node:url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const isWin = process.platform === "win32";
const args = process.argv.slice(2);

if (isWin) {
    const script = path.join(__dirname, "dev-start.ps1");

    // Try PowerShell Core (pwsh) first, fall back to Windows PowerShell (powershell)
    const tryPwsh = () => {
        const child = spawn("pwsh", ["-File", script, ...args], {
            stdio: "inherit",
        });
        child.on("exit", (code) => process.exit(code));
        child.on("error", (err) => {
            if (err && err.code === "ENOENT") {
                // pwsh not found — fallback
                tryWindowsPowerShell();
            } else {
                console.error("Failed to start pwsh:", err);
                process.exit(1);
            }
        });
    };

    const tryWindowsPowerShell = () => {
        // Use powershell.exe with Bypass execution policy
        const child = spawn(
            "powershell",
            ["-ExecutionPolicy", "Bypass", "-File", script, ...args],
            { stdio: "inherit" }
        );
        child.on("exit", (code) => process.exit(code));
        child.on("error", (err) => {
            console.error(
                "Failed to start any PowerShell (pwsh/powershell):",
                err
            );
            process.exit(1);
        });
    };

    tryPwsh();
} else {
    const sh = "sh";
    const script = path.join(__dirname, "dev-start.sh");
    const spawnArgs = [script, ...args];
    const child = spawn(sh, spawnArgs, { stdio: "inherit" });
    child.on("exit", (code) => process.exit(code));
}
