<?php
/**
 * 后台产品管理页面
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/models/Product.php';

$productModel = new Product();
$products = $productModel->getAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>产品管理 - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #0a0a0f; color: #fff; }
        .glass { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(10px); }
        .input-field { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 0.5rem 1rem; border-radius: 0.5rem; width: 100%; }
        .input-field:focus { outline: none; border-color: #8b5cf6; }
        .btn-primary { background: linear-gradient(135deg, #8b5cf6, #06b6d4); color: #fff; padding: 0.5rem 1.5rem; border-radius: 0.5rem; font-weight: 600; border: none; cursor: pointer; }
        .btn-primary:hover { opacity: 0.9; }
        .btn-danger { background: #ef4444; color: #fff; padding: 0.25rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; border: none; cursor: pointer; }
        .btn-edit { background: #3b82f6; color: #fff; padding: 0.25rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; border: none; cursor: pointer; }
        .badge { padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        .badge-online { background: rgba(16,185,129,0.2); color: #10b981; }
        .badge-updating { background: rgba(245,158,11,0.2); color: #f59e0b; }
        .badge-development { background: rgba(139,92,246,0.2); color: #8b5cf6; }
    </style>
</head>
<body class="min-h-screen p-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold">产品管理</h1>
            <a href="<?php echo SITE_URL; ?>/index.php" class="text-white/60 hover:text-white transition">← 返回首页</a>
        </div>

        <!-- 添加/编辑表单 -->
        <div class="glass rounded-2xl p-6 mb-8">
            <h2 class="text-xl font-bold mb-4" id="form-title">添加新产品</h2>
            <form id="product-form" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" id="edit-id" value="">
                <div>
                    <label class="block text-sm text-white/60 mb-1">产品名称 *</label>
                    <input type="text" id="f-name" class="input-field" required>
                </div>
                <div>
                    <label class="block text-sm text-white/60 mb-1">宣传语</label>
                    <input type="text" id="f-tagline" class="input-field">
                </div>
                <div>
                    <label class="block text-sm text-white/60 mb-1">状态</label>
                    <select id="f-status" class="input-field">
                        <option value="online">Online (已上线)</option>
                        <option value="updating">Updating (更新中)</option>
                        <option value="development">Development (开发中)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-white/60 mb-1">图片路径</label>
                    <input type="text" id="f-image" class="input-field" placeholder="/assets/images/hero-bg.jpg">
                </div>
                <div>
                    <label class="block text-sm text-white/60 mb-1">按钮文字</label>
                    <input type="text" id="f-button-text" class="input-field" value="Now Buy">
                </div>
                <div>
                    <label class="block text-sm text-white/60 mb-1">按钮链接</label>
                    <input type="text" id="f-button-link" class="input-field" placeholder="/partners.php">
                </div>
                <div>
                    <label class="block text-sm text-white/60 mb-1">排序权重</label>
                    <input type="number" id="f-sort-order" class="input-field" value="0">
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="f-is-visible" checked class="w-4 h-4">
                        <span class="text-sm text-white/60">前台可见</span>
                    </label>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm text-white/60 mb-1">功能特性（用 | 分隔）</label>
                    <input type="text" id="f-features" class="input-field" placeholder="100+ Features|Daily Updates|Undetected">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm text-white/60 mb-1">描述</label>
                    <textarea id="f-description" class="input-field" rows="2"></textarea>
                </div>
                <div class="md:col-span-2 flex gap-3">
                    <button type="submit" class="btn-primary" id="submit-btn">添加产品</button>
                    <button type="button" class="btn-primary" style="background: #6b7280;" id="cancel-btn" onclick="resetForm()" style="display:none;">取消编辑</button>
                </div>
            </form>
        </div>

        <!-- 产品列表 -->
        <div class="glass rounded-2xl p-6">
            <h2 class="text-xl font-bold mb-4">产品列表 (<?php echo count($products); ?>)</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-white/50 border-b border-white/10">
                            <th class="text-left py-3 px-4">ID</th>
                            <th class="text-left py-3 px-4">名称</th>
                            <th class="text-left py-3 px-4">宣传语</th>
                            <th class="text-left py-3 px-4">状态</th>
                            <th class="text-left py-3 px-4">排序</th>
                            <th class="text-left py-3 px-4">可见</th>
                            <th class="text-left py-3 px-4">操作</th>
                        </tr>
                    </thead>
                    <tbody id="product-list">
                        <?php foreach ($products as $p): ?>
                        <tr class="border-b border-white/5 hover:bg-white/5 transition" data-id="<?php echo $p['id']; ?>">
                            <td class="py-3 px-4 text-white/40"><?php echo $p['id']; ?></td>
                            <td class="py-3 px-4 font-medium"><?php echo htmlspecialchars($p['name']); ?></td>
                            <td class="py-3 px-4 text-white/60 max-w-xs truncate"><?php echo htmlspecialchars($p['tagline']); ?></td>
                            <td class="py-3 px-4"><span class="badge badge-<?php echo $p['status']; ?>"><?php echo ucfirst($p['status']); ?></span></td>
                            <td class="py-3 px-4 text-white/40"><?php echo $p['sort_order']; ?></td>
                            <td class="py-3 px-4"><?php echo $p['is_visible'] ? '✅' : '❌'; ?></td>
                            <td class="py-3 px-4 flex gap-2">
                                <button class="btn-edit" onclick="editProduct(<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES); ?>)">编辑</button>
                                <button class="btn-danger" onclick="deleteProduct(<?php echo $p['id']; ?>)">删除</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function resetForm() {
        document.getElementById('product-form').reset();
        document.getElementById('edit-id').value = '';
        document.getElementById('form-title').textContent = '添加新产品';
        document.getElementById('submit-btn').textContent = '添加产品';
        document.getElementById('cancel-btn').style.display = 'none';
        document.getElementById('f-button-text').value = 'Now Buy';
        document.getElementById('f-is-visible').checked = true;
    }

    function editProduct(p) {
        document.getElementById('edit-id').value = p.id;
        document.getElementById('f-name').value = p.name;
        document.getElementById('f-tagline').value = p.tagline;
        document.getElementById('f-description').value = p.description || '';
        document.getElementById('f-status').value = p.status;
        document.getElementById('f-image').value = p.image;
        document.getElementById('f-button-text').value = p.button_text;
        document.getElementById('f-button-link').value = p.button_link;
        document.getElementById('f-features').value = p.features;
        document.getElementById('f-sort-order').value = p.sort_order;
        document.getElementById('f-is-visible').checked = p.is_visible == 1;
        document.getElementById('form-title').textContent = '编辑产品: ' + p.name;
        document.getElementById('submit-btn').textContent = '保存修改';
        document.getElementById('cancel-btn').style.display = 'inline-block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.getElementById('product-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('edit-id').value;
        const action = id ? 'update' : 'create';
        const body = new URLSearchParams({
            action: action,
            name: document.getElementById('f-name').value,
            tagline: document.getElementById('f-tagline').value,
            description: document.getElementById('f-description').value,
            status: document.getElementById('f-status').value,
            image: document.getElementById('f-image').value,
            button_text: document.getElementById('f-button-text').value,
            button_link: document.getElementById('f-button-link').value,
            features: document.getElementById('f-features').value,
            sort_order: document.getElementById('f-sort-order').value,
            is_visible: document.getElementById('f-is-visible').checked ? 1 : 0,
        });
        if (id) body.append('id', id);

        fetch('<?php echo SITE_URL; ?>/api/products.php', {
            method: 'POST',
            body: body
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert(id ? '产品已更新' : '产品已添加');
                location.reload();
            } else {
                alert(data.message || '操作失败');
            }
        })
        .catch(() => alert('网络错误'));
    });

    function deleteProduct(id) {
        if (!confirm('确定要删除这个产品吗？')) return;
        fetch('<?php echo SITE_URL; ?>/api/products.php', {
            method: 'POST',
            body: 'action=delete&id=' + id
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '删除失败');
            }
        })
        .catch(() => alert('网络错误'));
    }
    </script>
</body>
</html>
