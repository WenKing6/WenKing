<?php
/**
 * 应用页面底部模板
 * 包含 GridScan 初始化和 app.js 引入
 */
?>

    <!-- GridScan 背景容器 -->
    <div id="grid-scan-bg" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1; pointer-events: none;"></div>

    <!-- Scripts -->
    <script src="<?php echo SITE_URL; ?>/assets/js/grid-scan.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/app.js"></script>

    <!-- GridScan 初始化 -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('grid-scan-bg');
        if (container && typeof GridScan !== 'undefined') {
            // 随机深色（网格线）
            function randomDarkColor() {
                const r = Math.floor(Math.random() * 60 + 20);
                const g = Math.floor(Math.random() * 60 + 20);
                const b = Math.floor(Math.random() * 80 + 30);
                return `rgb(${r}, ${g}, ${b})`;
            }

            // 随机亮色（扫描光）
            function randomBrightColor() {
                const palettes = [
                    ['#8b5cf6', '#a78bfa'], // 紫色系
                    ['#3b82f6', '#60a5fa'], // 蓝色系
                    ['#06b6d4', '#22d3ee'], // 青色系
                    ['#ec4899', '#f472b6'], // 粉色系
                    ['#10b981', '#34d399'], // 绿色系
                    ['#f59e0b', '#fbbf24'], // 橙色系
                    ['#ef4444', '#f87171'], // 红色系
                ];
                const palette = palettes[Math.floor(Math.random() * palettes.length)];
                return palette[Math.floor(Math.random() * palette.length)];
            }

            const gridScan = new GridScan(container, {
                lineThickness: 1,
                linesColor: randomDarkColor(),
                scanColor: randomBrightColor(),
                scanOpacity: 0.4,
                gridScale: 0.1,
                lineStyle: 'solid',
                lineJitter: 0.1,
                scanDirection: 'pingpong',
                noiseIntensity: 0.01,
                scanGlow: 0.5,
                scanSoftness: 2,
                scanPhaseTaper: 0.9,
                scanDuration: 2.0,
                scanDelay: 2.0,
                sensitivity: 0.55
            });

            // 每 4 秒切换一次颜色
            setInterval(function() {
                gridScan.setColors(randomDarkColor(), randomBrightColor());
            }, 4000);
        }
    });
    </script>
</body>
</html>
