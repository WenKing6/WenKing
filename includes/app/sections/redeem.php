<?php
/**
 * 兑换码页面内容
 */
?>
<div class="app-page-header mb-8">
    <h1 class="text-3xl font-display font-bold mb-2">
        <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent">Redeem Code</span>
    </h1>
    <p class="text-white/60">Enter your redemption code to activate your subscription.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 w-full">
    <!-- 兑换表单 -->
    <div class="glass-card p-8 rounded-xl">
        <div class="space-y-6">
            <!-- 输入框 -->
            <div>
                <label class="block text-sm text-white/60 mb-2">Redemption Code</label>
                <input type="text" placeholder="Enter your code here" class="app-input w-full text-center text-lg tracking-widest uppercase" id="redeem-code-input">
            </div>

            <!-- 按钮 -->
            <button class="btn-primary w-full py-3 rounded-lg font-semibold text-center" id="redeem-btn">
                Redeem
            </button>

            <!-- 提示信息 -->
            <div id="redeem-message" class="hidden text-center text-sm py-3 rounded-lg"></div>
        </div>
    </div>

    <!-- 使用说明 -->
    <div class="glass-card p-6 rounded-xl">
        <h3 class="text-sm font-semibold text-white/80 mb-3">How to redeem?</h3>
        <ol class="space-y-2 text-sm text-white/50 list-decimal list-inside">
            <li>Purchase a subscription from our Discord server</li>
            <li>Copy the redemption code you received</li>
            <li>Paste the code in the input field above</li>
            <li>Click "Redeem" to activate your subscription</li>
        </ol>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('redeem-btn');
    var input = document.getElementById('redeem-code-input');
    var msg = document.getElementById('redeem-message');

    if (btn && input && msg) {
        btn.addEventListener('click', function() {
            var code = input.value.trim();
            if (!code) {
                msg.textContent = 'Please enter a redemption code.';
                msg.className = 'text-center text-sm py-3 rounded-lg bg-red-500/10 text-red-400';
                msg.classList.remove('hidden');
                return;
            }

            // 模拟兑换（后续接入后端 API）
            msg.textContent = 'Code "' + code + '" submitted. Feature coming soon!';
            msg.className = 'text-center text-sm py-3 rounded-lg bg-accent-purple/10 text-accent-purple';
            msg.classList.remove('hidden');
        });
    }
});
</script>
