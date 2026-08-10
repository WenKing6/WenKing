<?php
/**
 * Manager 页面 - 管理员控制面板
 */
?>
<div class="app-page-header mb-8">
    <h1 class="text-3xl font-display font-bold mb-2">
        <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent"><?php _e('manager.title'); ?></span>
    </h1>
    <p class="text-white/60"><?php _e('manager.subtitle'); ?></p>
</div>

<!-- Tab 导航 -->
<div class="manager-tabs mb-6 flex gap-2 border-b border-white/10">
    <button class="manager-tab active px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="users">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
        </svg>
        <?php _e('manager.user_management'); ?>
    </button>
    <button class="manager-tab px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="licenses">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
        </svg>
        <?php _e('manager.license_management'); ?>
    </button>
</div>

<!-- Tab 内容区域 -->
<div class="manager-tab-content">
    <!-- Users Tab -->
    <div id="users-tab" class="tab-panel active">
        <!-- 统计卡片 -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
            <div class="glass-card p-6 rounded-xl">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-accent-purple/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-accent-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-white/60"><?php _e('manager.total_users'); ?></span>
                </div>
                <div class="text-3xl font-bold text-white">1,247</div>
            </div>

            <div class="glass-card p-6 rounded-xl">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-accent-cyan/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-accent-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-white/60"><?php _e('manager.resellers'); ?></span>
                </div>
                <div class="text-3xl font-bold text-white">23</div>
            </div>

            <div class="glass-card p-6 rounded-xl">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-status-online/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-status-online" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-white/60"><?php _e('manager.admins'); ?></span>
                </div>
                <div class="text-3xl font-bold text-white">5</div>
            </div>
        </div>

        <!-- 用户列表 - 卡片式布局 -->
        <div class="glass-card p-6 rounded-xl">
            <div class="flex flex-col md:flex-row gap-4 mb-6">
                <div class="flex-1">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>
                        <input type="text" 
                               class="app-input w-full pl-10 pr-4 py-2"
                               placeholder="<?php _e('manager.search_users'); ?>">
                    </div>
                </div>
                <div class="flex gap-2">
                    <select class="app-select">
                        <option value=""><?php _e('manager.all_roles'); ?></option>
                        <option value="admin"><?php _e('manager.admin'); ?></option>
                        <option value="reseller"><?php _e('manager.reseller'); ?></option>
                        <option value="user"><?php _e('manager.user'); ?></option>
                    </select>
                    <button class="btn-primary px-6 py-2 rounded-lg font-semibold whitespace-nowrap">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <?php _e('manager.add_user'); ?>
                    </button>
                </div>
            </div>

            <div class="space-y-3">
                <!-- Admin 用户卡片 -->
                <div class="p-4 rounded-lg bg-white/5 hover:bg-white/10 transition">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-full bg-status-online/20 flex items-center justify-center shrink-0">
                                <span class="text-sm font-semibold text-status-online">AD</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-white truncate">AdminUser</div>
                                <div class="text-xs text-white/60 truncate hidden sm:block">admin@wenking.com</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-status-online/20 text-status-online hidden sm:inline-block"><?php _e('manager.admin'); ?></span>
                            <span class="status-badge status-online text-xs"><?php _e('manager.active'); ?></span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <button class="text-white/40 hover:text-accent-purple transition p-2 rounded-lg hover:bg-white/5" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                            <button class="text-white/40 hover:text-accent-blue transition p-2 rounded-lg hover:bg-white/5" title="Edit Role">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Reseller 用户卡片 -->
                <div class="p-4 rounded-lg bg-white/5 hover:bg-white/10 transition">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-full bg-accent-cyan/20 flex items-center justify-center shrink-0">
                                <span class="text-sm font-semibold text-accent-cyan">RS</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-white truncate">ResellerPro</div>
                                <div class="text-xs text-white/60 truncate hidden sm:block">reseller@example.com</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-accent-cyan/20 text-accent-cyan hidden sm:inline-block"><?php _e('manager.reseller'); ?></span>
                            <span class="status-badge status-online text-xs"><?php _e('manager.active'); ?></span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <button class="text-white/40 hover:text-accent-purple transition p-2 rounded-lg hover:bg-white/5" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                            <button class="text-white/40 hover:text-accent-blue transition p-2 rounded-lg hover:bg-white/5" title="Edit Role">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button class="text-white/40 hover:text-red-500 transition p-2 rounded-lg hover:bg-white/5" title="Disable">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 普通用户卡片 -->
                <div class="p-4 rounded-lg bg-white/5 hover:bg-white/10 transition">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-full bg-accent-purple/20 flex items-center justify-center shrink-0">
                                <span class="text-sm font-semibold text-accent-purple">JD</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-white truncate">JohnDoe</div>
                                <div class="text-xs text-white/60 truncate hidden sm:block">john@example.com</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-accent-purple/20 text-accent-purple hidden sm:inline-block"><?php _e('manager.user'); ?></span>
                            <span class="status-badge status-online text-xs"><?php _e('manager.active'); ?></span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <button class="text-white/40 hover:text-accent-purple transition p-2 rounded-lg hover:bg-white/5" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                            <button class="text-white/40 hover:text-accent-blue transition p-2 rounded-lg hover:bg-white/5" title="Edit Role">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button class="text-white/40 hover:text-red-500 transition p-2 rounded-lg hover:bg-white/5" title="Disable">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-white/60"><?php _e('manager.showing'); ?></div>
                <div class="flex gap-2">
                    <button class="px-3 py-1 rounded bg-white/5 text-white/40 cursor-not-allowed"><?php _e('manager.previous'); ?></button>
                    <button class="px-3 py-1 rounded bg-white/5 text-white/40 cursor-not-allowed"><?php _e('manager.next'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Licenses Tab -->
    <div id="licenses-tab" class="tab-panel">
        <!-- 统计卡片 -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 mb-6">
            <div class="glass-card p-6 rounded-xl">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-accent-purple/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-accent-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-white/60"><?php _e('manager.total_licenses'); ?></span>
                </div>
                <div class="text-3xl font-bold text-white">3,892</div>
            </div>

            <div class="glass-card p-6 rounded-xl">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-status-online/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-status-online" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-white/60"><?php _e('manager.active'); ?></span>
                </div>
                <div class="text-3xl font-bold text-white">2,156</div>
            </div>

            <div class="glass-card p-6 rounded-xl">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-status-updating/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-status-updating" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-white/60"><?php _e('manager.expired'); ?></span>
                </div>
                <div class="text-3xl font-bold text-white">1,689</div>
            </div>

            <div class="glass-card p-6 rounded-xl">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-red-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-white/60"><?php _e('manager.revoked'); ?></span>
                </div>
                <div class="text-3xl font-bold text-white">47</div>
            </div>
        </div>

        <!-- 生成许可证 -->
        <div class="glass-card p-6 rounded-xl mb-6">
            <h3 class="text-lg font-semibold mb-4 text-white"><?php _e('manager.generate_new'); ?></h3>
            <form class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2"><?php _e('manager.product'); ?></label>
                    <select class="app-select w-full">
                        <option>GTA V Menu</option>
                        <option>RDR2 Mod</option>
                        <option>GTA VI Beta</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2"><?php _e('manager.duration'); ?></label>
                    <select class="app-select w-full">
                        <option><?php _e('manager.days_7'); ?></option>
                        <option><?php _e('manager.days_30'); ?></option>
                        <option><?php _e('manager.days_90'); ?></option>
                        <option><?php _e('manager.year_1'); ?></option>
                        <option><?php _e('manager.lifetime'); ?></option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2"><?php _e('manager.quantity'); ?></label>
                    <input type="number" 
                           class="app-input w-full px-4 py-2"
                           placeholder="<?php _e('manager.quantity_ph'); ?>"
                           value="10"
                           min="1"
                           max="1000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2"><?php _e('manager.assign_to'); ?></label>
                    <input type="text" 
                           class="app-input w-full px-4 py-2"
                           placeholder="<?php _e('manager.assign_ph'); ?>">
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="btn-primary px-6 py-2 rounded-lg font-semibold">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <?php _e('manager.generate_btn'); ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- 许可证列表 - 卡片式布局 -->
        <div class="glass-card p-6 rounded-xl">
            <div class="flex flex-col md:flex-row gap-4 mb-6">
                <div class="flex-1">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>
                        <input type="text" 
                               class="app-input w-full pl-10 pr-4 py-2"
                               placeholder="<?php _e('manager.search_licenses'); ?>">
                    </div>
                </div>
                <div class="flex gap-2">
                    <select class="app-select">
                        <option value=""><?php _e('manager.all_status'); ?></option>
                        <option value="active"><?php _e('manager.active'); ?></option>
                        <option value="expired"><?php _e('manager.expired'); ?></option>
                        <option value="revoked"><?php _e('manager.revoked'); ?></option>
                    </select>
                    <button class="px-6 py-2 rounded-lg font-semibold bg-white/5 hover:bg-white/10 text-white/80 transition whitespace-nowrap">
                        <?php _e('manager.export_csv'); ?>
                    </button>
                </div>
            </div>

            <div class="space-y-3">
                <!-- 许可证卡片 1 -->
                <div class="p-4 rounded-lg bg-white/5 hover:bg-white/10 transition">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-lg bg-accent-cyan/20 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-accent-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-white truncate">GTA V Menu</div>
                                <code class="text-xs bg-white/10 px-2 py-0.5 rounded text-accent-cyan inline-block truncate max-w-[180px]">WK-2024-AAAA-1111</code>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <div class="text-xs text-white/50 hidden sm:block">
                                <span><?php _e('manager.user_label'); ?>: JohnDoe</span>
                            </div>
                            <span class="status-badge status-online text-xs"><?php _e('manager.active'); ?></span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <button class="text-white/40 hover:text-accent-purple transition p-2 rounded-lg hover:bg-white/5" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                            <button class="text-white/40 hover:text-red-500 transition p-2 rounded-lg hover:bg-white/5" title="Revoke">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 mt-3 pt-3 border-t border-white/5 text-xs text-white/40 sm:hidden">
                        <span><?php _e('manager.user_label'); ?>: JohnDoe</span>
                        <span><?php _e('manager.expires'); ?>: 2025-01-15</span>
                    </div>
                </div>

                <!-- 许可证卡片 2 -->
                <div class="p-4 rounded-lg bg-white/5 hover:bg-white/10 transition">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-lg bg-amber-500/20 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-white truncate">RDR2 Mod</div>
                                <code class="text-xs bg-white/10 px-2 py-0.5 rounded text-accent-cyan inline-block truncate max-w-[180px]">WK-2024-BBBB-2222</code>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <div class="text-xs text-white/50 hidden sm:block">
                                <span><?php _e('manager.user_label'); ?>: AliceSmith</span>
                            </div>
                            <span class="status-badge status-updating text-xs"><?php _e('manager.expired'); ?></span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <button class="text-white/40 hover:text-accent-purple transition p-2 rounded-lg hover:bg-white/5" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                            <button class="text-white/40 hover:text-red-500 transition p-2 rounded-lg hover:bg-white/5" title="Revoke">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 mt-3 pt-3 border-t border-white/5 text-xs text-white/40 sm:hidden">
                        <span><?php _e('manager.user_label'); ?>: AliceSmith</span>
                        <span><?php _e('manager.expires'); ?>: 2024-08-20</span>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-white/60"><?php _e('manager.showing_2'); ?></div>
                <div class="flex gap-2">
                    <button class="px-3 py-1 rounded bg-white/5 text-white/40 cursor-not-allowed"><?php _e('manager.previous'); ?></button>
                    <button class="px-3 py-1 rounded bg-white/5 text-white/40 cursor-not-allowed"><?php _e('manager.next'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
