import chokidar from 'chokidar';
import { execSync } from 'child_process';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const BASE = __dirname;
const WATCH_DIRS = ['app', 'config', 'database', 'resources', 'routes', 'public/assets'];
const EXCLUDE = [/[/\\]vendor[/\\]/, /[/\\]node_modules[/\\]/, /[/\\]storage[/\\]/, /[/\\]\.git[/\\]/,
  /\.log$/, /\.DS_Store$/, /[/\\]bootstrap[/\\]cache[/\\]/];

const debounceTimers = {};

const watcher = chokidar.watch(WATCH_DIRS.map(d => path.join(BASE, d)), {
  ignored: (p) => EXCLUDE.some(r => r.test(p)),
  persistent: true,
  ignoreInitial: true,
  awaitWriteFinish: { stabilityThreshold: 500, pollInterval: 100 },
});

function runAudit(filePath) {
  const rel = path.relative(BASE, filePath).replace(/\\/g, '/');
  if (debounceTimers[rel]) clearTimeout(debounceTimers[rel]);
  debounceTimers[rel] = setTimeout(() => {
    delete debounceTimers[rel];
    try {
      execSync('php artisan file:audit --quiet', { cwd: BASE, stdio: 'ignore' });
    } catch (_) {}
  }, 1000);
}

watcher.on('add', runAudit).on('change', runAudit).on('unlink', runAudit);

console.log('Watching for file changes...');

process.on('SIGINT', () => { watcher.close(); process.exit(); });
process.on('SIGTERM', () => { watcher.close(); process.exit(); });
