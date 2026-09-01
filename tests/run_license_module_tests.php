<?php
/**
 * ============================================================
 * LicenseModule 单元测试（零依赖，纯 PHP 运行）
 * ============================================================
 * 运行方式：php tests/run_license_module_tests.php
 * 退出码：0 = 全部通过；1 = 存在失败
 *
 * 覆盖范围（均为不依赖数据库的纯逻辑）：
 *   1. LicenseModule::can()               —— 角色权限矩阵（授权/领取/回收/删除/生成）
 *   2. LicenseModule::getUiVisibility()   —— 各角色页面可见内容配置
 *   3. LicenseModule::evaluateManagerTransfer() —— 经理划转额度校验
 *   4. 权限矩阵与 UI 可见性的一致性校验（同源保证）
 *
 * 数据库相关路径（grantQuota / claim 的事务）不在本文件覆盖范围，
 * 由「Manager/Reseller 页面手工验收清单」覆盖（见交付说明）。
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/services/LicenseModule.php';

$passCount = 0;
$failCount = 0;
$failures = [];

function check(string $name, bool $cond): void {
    global $passCount, $failCount, $failures;
    if ($cond) {
        $passCount++;
        echo "  [PASS] {$name}\n";
    } else {
        $failCount++;
        $failures[] = $name;
        echo "  [FAIL] {$name}\n";
    }
}

$roles = ['admin', 'manager', 'reseller', 'user'];

// ============================================================
echo "== 1. 权限矩阵 LicenseModule::can() ==\n";
// ============================================================

// 1.1 领取钥匙（Create License）：仅 manager / reseller
check('admin 不可领取（直接拥有库存）', !LicenseModule::can('admin', LicenseModule::ACTION_CLAIM));
check('manager 可领取', LicenseModule::can('manager', LicenseModule::ACTION_CLAIM));
check('reseller 可领取', LicenseModule::can('reseller', LicenseModule::ACTION_CLAIM));
check('user 不可领取', !LicenseModule::can('user', LicenseModule::ACTION_CLAIM));
check('未知角色不可领取', !LicenseModule::can('hacker', LicenseModule::ACTION_CLAIM));

// 1.2 授权配额：admin → manager/reseller；manager → reseller；reseller/user → 无人
check('admin 可授权给 manager', LicenseModule::can('admin', LicenseModule::ACTION_GRANT_QUOTA, 'manager'));
check('admin 可授权给 reseller', LicenseModule::can('admin', LicenseModule::ACTION_GRANT_QUOTA, 'reseller'));
check('admin 不可授权给 user', !LicenseModule::can('admin', LicenseModule::ACTION_GRANT_QUOTA, 'user'));
check('admin 不可授权给自己角色', !LicenseModule::can('admin', LicenseModule::ACTION_GRANT_QUOTA, 'admin'));
check('manager 可授权给 reseller（划转）', LicenseModule::can('manager', LicenseModule::ACTION_GRANT_QUOTA, 'reseller'));
check('manager 不可授权给 manager', !LicenseModule::can('manager', LicenseModule::ACTION_GRANT_QUOTA, 'manager'));
check('manager 不可授权给 admin', !LicenseModule::can('manager', LicenseModule::ACTION_GRANT_QUOTA, 'admin'));
check('manager 不可授权给 user', !LicenseModule::can('manager', LicenseModule::ACTION_GRANT_QUOTA, 'user'));
check('reseller 不可授权给任何角色', !LicenseModule::can('reseller', LicenseModule::ACTION_GRANT_QUOTA, 'manager')
    && !LicenseModule::can('reseller', LicenseModule::ACTION_GRANT_QUOTA, 'reseller')
    && !LicenseModule::can('reseller', LicenseModule::ACTION_GRANT_QUOTA, 'user'));
check('user 不可授权给任何角色', !LicenseModule::can('user', LicenseModule::ACTION_GRANT_QUOTA, 'reseller'));
check('授权动作缺少目标角色时拒绝', !LicenseModule::can('admin', LicenseModule::ACTION_GRANT_QUOTA, null));
check('未知角色不可授权', !LicenseModule::can('hacker', LicenseModule::ACTION_GRANT_QUOTA, 'reseller'));

// 1.3 其他管理动作：仅 admin
check('仅 admin 可回收钥匙', LicenseModule::can('admin', LicenseModule::ACTION_RECYCLE)
    && !LicenseModule::can('manager', LicenseModule::ACTION_RECYCLE)
    && !LicenseModule::can('reseller', LicenseModule::ACTION_RECYCLE)
    && !LicenseModule::can('user', LicenseModule::ACTION_RECYCLE));
check('仅 admin 可删除配额', LicenseModule::can('admin', LicenseModule::ACTION_DELETE_ALLOCATION)
    && !LicenseModule::can('manager', LicenseModule::ACTION_DELETE_ALLOCATION)
    && !LicenseModule::can('reseller', LicenseModule::ACTION_DELETE_ALLOCATION));
check('仅 admin 可生成新钥匙入库', LicenseModule::can('admin', LicenseModule::ACTION_GENERATE)
    && !LicenseModule::can('manager', LicenseModule::ACTION_GENERATE)
    && !LicenseModule::can('reseller', LicenseModule::ACTION_GENERATE));

// ============================================================
echo "== 2. 页面可见性 LicenseModule::getUiVisibility() ==\n";
// ============================================================

$adminVis = LicenseModule::getUiVisibility('admin');
$managerVis = LicenseModule::getUiVisibility('manager');
$resellerVis = LicenseModule::getUiVisibility('reseller');
$userVis = LicenseModule::getUiVisibility('user');
$unknownVis = LicenseModule::getUiVisibility('hacker');

check('admin 显示配额面板（授权入口）', $adminVis['quota_panel'] === true);
check('admin 不显示 Create License（其拥有库存）', $adminVis['create_license_btn'] === false);
check('admin 授权目标为 manager+reseller', $adminVis['grant_quota_targets'] === ['manager', 'reseller']);
check('admin 钥匙范围为 all（审计）', $adminVis['license_scope'] === 'all');
check('admin 显示 Add License 入口', $adminVis['add_license'] === true);

check('manager 显示配额面板', $managerVis['quota_panel'] === true);
check('manager 显示 Create License 按钮', $managerVis['create_license_btn'] === true);
check('manager 授权目标仅 reseller', $managerVis['grant_quota_targets'] === ['reseller']);
check('manager 钥匙范围为 own', $managerVis['license_scope'] === 'own');
check('manager 无 Add License 入口', $managerVis['add_license'] === false);

check('reseller 显示配额面板', $resellerVis['quota_panel'] === true);
check('reseller 显示 Create License 按钮', $resellerVis['create_license_btn'] === true);
check('reseller 无授权按钮（目标为空）', $resellerVis['grant_quota_targets'] === []);
check('reseller 钥匙范围为 own', $resellerVis['license_scope'] === 'own');

check('user 无配额面板', $userVis['quota_panel'] === false);
check('user 无 Create License 按钮', $userVis['create_license_btn'] === false);
check('user 钥匙范围为 none', $userVis['license_scope'] === 'none');

check('未知角色一切不可见（安全默认值）', $unknownVis['quota_panel'] === false
    && $unknownVis['create_license_btn'] === false
    && $unknownVis['license_scope'] === 'none'
    && $unknownVis['grant_quota_targets'] === []);

// ============================================================
echo "== 3. 经理划转校验 LicenseModule::evaluateManagerTransfer() ==\n";
// ============================================================

$r1 = LicenseModule::evaluateManagerTransfer(null, 5);
check('无配额时拒绝划转', $r1['ok'] === false);

$r2 = LicenseModule::evaluateManagerTransfer(['quantity' => 10, 'used_count' => 4], 0);
check('数量为 0 时拒绝', $r2['ok'] === false);

$r3 = LicenseModule::evaluateManagerTransfer(['quantity' => 10, 'used_count' => 4], -3);
check('数量为负时拒绝', $r3['ok'] === false);

$r4 = LicenseModule::evaluateManagerTransfer(['quantity' => 10, 'used_count' => 8], 5);
check('超出剩余额度时拒绝（剩余 2 < 申请 5）', $r4['ok'] === false && strpos($r4['message'], 'only 2') !== false);

$r5 = LicenseModule::evaluateManagerTransfer(['quantity' => 10, 'used_count' => 5], 5);
check('恰好等于剩余额度时允许（划转后剩余 0）', $r5['ok'] === true && $r5['remaining'] === 0);

$r6 = LicenseModule::evaluateManagerTransfer(['quantity' => 10, 'used_count' => 3], 3);
check('正常划转（划转前剩余 7，划转后剩余 4）', $r6['ok'] === true && $r6['remaining'] === 4);

// ============================================================
echo "== 4. 权限矩阵与 UI 可见性一致性（同源保证） ==\n";
// ============================================================

foreach ($roles as $role) {
    $vis = LicenseModule::getUiVisibility($role);
    // Create License 按钮可见性必须等于 can(claim)
    check("[{$role}] create_license_btn 与 can(claim) 一致",
        $vis['create_license_btn'] === LicenseModule::can($role, LicenseModule::ACTION_CLAIM));

    // 授权目标一致性：对每个已知角色，can(grant) ⟺ 出现在可见目标列表
    $consistent = true;
    foreach ($roles as $target) {
        $allowed = LicenseModule::can($role, LicenseModule::ACTION_GRANT_QUOTA, $target);
        $listed = in_array($target, $vis['grant_quota_targets'], true);
        if ($allowed !== $listed) {
            $consistent = false;
        }
    }
    check("[{$role}] 授权目标列表与 can(grant, target) 完全一致", $consistent);
}

// ============================================================
echo "\n========================================\n";
echo "结果: {$passCount} 通过, {$failCount} 失败\n";
echo "========================================\n";

if ($failCount > 0) {
    echo "失败用例:\n";
    foreach ($failures as $f) {
        echo "  - {$f}\n";
    }
    exit(1);
}
exit(0);
