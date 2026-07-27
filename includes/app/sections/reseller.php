<?php
/**
 * Reseller 页面 - 分销商管理面板
 */
?>
<div class="app-page-header mb-8">
    <h1 class="text-3xl font-display font-bold mb-2">
        <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent">Reseller Panel</span>
    </h1>
    <p class="text-white/60">Manage your customers, licenses, and reseller settings.</p>
</div>

<!-- Tab 导航 -->
<div class="reseller-tabs mb-6 flex gap-2 border-b border-white/10">
    <button class="reseller-tab active px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="customers">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        Customers
    </button>
    <button class="reseller-tab px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="licenses">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
        </svg>
        Licenses
    </button>
    <button class="reseller-tab px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="settings">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
        Settings
    </button>
</div>

<!-- Tab 内容区域 -->
<div class="reseller-tab-content">
    <!-- Customers Tab -->
    <div id="customers-tab" class="tab-panel active">
        <!-- 搜索和筛选 -->
        <div class="glass-card p-4 rounded-xl mb-6">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>
                        <input type="text" 
                               id="customer-search"
                               class="auth-input w-full pl-10 pr-4 py-2 rounded-lg"
                               placeholder="Search by username, email, or license key...">
                    </div>
                </div>
                <div class="flex gap-2">
                    <select class="auth-input px-4 py-2 rounded-lg">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="banned">Banned</option>
                    </select>
                    <button class="btn-primary px-6 py-2 rounded-lg font-semibold whitespace-nowrap">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Customer
                    </button>
                </div>
            </div>
        </div>

        <!-- 客户列表 - 卡片式布局 -->
        <div class="space-y-4">
            <!-- 客户卡片 1 -->
            <div class="glass-card p-6 rounded-xl hover:border-accent-purple/50 transition">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- 用户信息 -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-accent-purple/20 flex items-center justify-center shrink-0">
                            <span class="text-sm font-semibold text-accent-purple">JD</span>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-white">JohnDoe</div>
                            <div class="text-xs text-white/60">john@example.com</div>
                        </div>
                    </div>
                    
                    <!-- 许可证 -->
                    <div>
                        <div class="text-xs text-white/40 mb-1">License</div>
                        <code class="text-xs bg-white/10 px-2 py-1 rounded text-accent-cyan">WK-2024-XXXX-XXXX</code>
                    </div>
                    
                    <!-- 时间信息 -->
                    <div>
                        <div class="text-xs text-white/40 mb-1">Registered</div>
                        <div class="text-sm text-white">2024-01-15</div>
                        <div class="text-xs text-white/60">Last login: 2 hours ago</div>
                    </div>
                    
                    <!-- IP 和机器码 -->
                    <div>
                        <div class="text-xs text-white/40 mb-1">IP Address</div>
                        <div class="text-sm text-white font-mono">192.168.1.100</div>
                        <div class="text-xs text-white/60 font-mono">MCH-8F3A2B1C</div>
                    </div>
                </div>
                
                <!-- 底部操作栏 -->
                <div class="flex items-center justify-between mt-4 pt-4 border-t border-white/10">
                    <span class="status-badge status-online text-xs">Active</span>
                    <div class="flex gap-2">
                        <button class="text-white/40 hover:text-accent-purple transition p-2 rounded-lg hover:bg-white/5" title="View Details">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                        <button class="text-white/40 hover:text-accent-blue transition p-2 rounded-lg hover:bg-white/5" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button class="text-white/40 hover:text-red-500 transition p-2 rounded-lg hover:bg-white/5" title="Disable">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 客户卡片 2 -->
            <div class="glass-card p-6 rounded-xl hover:border-accent-purple/50 transition">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-accent-blue/20 flex items-center justify-center shrink-0">
                            <span class="text-sm font-semibold text-accent-blue">AS</span>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-white">AliceSmith</div>
                            <div class="text-xs text-white/60">alice@example.com</div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="text-xs text-white/40 mb-1">License</div>
                        <code class="text-xs bg-white/10 px-2 py-1 rounded text-accent-cyan">WK-2024-YYYY-YYYY</code>
                    </div>
                    
                    <div>
                        <div class="text-xs text-white/40 mb-1">Registered</div>
                        <div class="text-sm text-white">2024-02-20</div>
                        <div class="text-xs text-white/60">Last login: 1 day ago</div>
                    </div>
                    
                    <div>
                        <div class="text-xs text-white/40 mb-1">IP Address</div>
                        <div class="text-sm text-white font-mono">10.0.0.50</div>
                        <div class="text-xs text-white/60 font-mono">MCH-9D4E5F6G</div>
                    </div>
                </div>
                
                <div class="flex items-center justify-between mt-4 pt-4 border-t border-white/10">
                    <span class="status-badge status-online text-xs">Active</span>
                    <div class="flex gap-2">
                        <button class="text-white/40 hover:text-accent-purple transition p-2 rounded-lg hover:bg-white/5" title="View Details">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                        <button class="text-white/40 hover:text-accent-blue transition p-2 rounded-lg hover:bg-white/5" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button class="text-white/40 hover:text-red-500 transition p-2 rounded-lg hover:bg-white/5" title="Disable">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 客户卡片 3 -->
            <div class="glass-card p-6 rounded-xl hover:border-accent-purple/50 transition">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-accent-cyan/20 flex items-center justify-center shrink-0">
                            <span class="text-sm font-semibold text-accent-cyan">BW</span>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-white">BobWilson</div>
                            <div class="text-xs text-white/60">bob@example.com</div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="text-xs text-white/40 mb-1">License</div>
                        <code class="text-xs bg-white/10 px-2 py-1 rounded text-accent-cyan">WK-2024-ZZZZ-ZZZZ</code>
                    </div>
                    
                    <div>
                        <div class="text-xs text-white/40 mb-1">Registered</div>
                        <div class="text-sm text-white">2024-03-10</div>
                        <div class="text-xs text-white/60">Last login: 3 days ago</div>
                    </div>
                    
                    <div>
                        <div class="text-xs text-white/40 mb-1">IP Address</div>
                        <div class="text-sm text-white font-mono">172.16.0.25</div>
                        <div class="text-xs text-white/60 font-mono">MCH-7H8I9J0K</div>
                    </div>
                </div>
                
                <div class="flex items-center justify-between mt-4 pt-4 border-t border-white/10">
                    <span class="status-badge status-updating text-xs">Inactive</span>
                    <div class="flex gap-2">
                        <button class="text-white/40 hover:text-accent-purple transition p-2 rounded-lg hover:bg-white/5" title="View Details">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                        <button class="text-white/40 hover:text-accent-blue transition p-2 rounded-lg hover:bg-white/5" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button class="text-white/40 hover:text-red-500 transition p-2 rounded-lg hover:bg-white/5" title="Disable">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 分页 -->
        <div class="mt-6 flex items-center justify-between">
            <div class="text-sm text-white/60">Showing 1 to 3 of 3 entries</div>
            <div class="flex gap-2">
                <button class="px-3 py-1 rounded bg-white/5 text-white/40 cursor-not-allowed">Previous</button>
                <button class="px-3 py-1 rounded bg-white/5 text-white/40 cursor-not-allowed">Next</button>
            </div>
        </div>
    </div>

    <!-- Licenses Tab -->
    <div id="licenses-tab" class="tab-panel">
        <!-- 批量查询 -->
        <div class="glass-card p-6 rounded-xl mb-6">
            <h3 class="text-lg font-semibold mb-4 text-white">License Lookup</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">Single License Query</label>
                    <div class="flex gap-2">
                        <input type="text" 
                               class="auth-input flex-1 px-4 py-2 rounded-lg"
                               placeholder="Enter license key (e.g., WK-2024-XXXX-XXXX)">
                        <button class="btn-primary px-6 py-2 rounded-lg font-semibold whitespace-nowrap">
                            Query
                        </button>
                    </div>
                </div>
                <div class="border-t border-white/10 pt-4">
                    <label class="block text-sm font-medium text-white/70 mb-2">Batch Query (One per line)</label>
                    <textarea class="auth-input w-full px-4 py-2 rounded-lg h-32 resize-none"
                              placeholder="WK-2024-XXXX-XXXX&#10;WK-2024-YYYY-YYYY&#10;WK-2024-ZZZZ-ZZZZ"></textarea>
                    <div class="flex gap-2 mt-2">
                        <button class="btn-primary px-6 py-2 rounded-lg font-semibold">
                            Batch Query
                        </button>
                        <button class="px-6 py-2 rounded-lg font-semibold bg-white/5 hover:bg-white/10 text-white/80 transition">
                            Export Results
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 许可证分配记录 - 卡片式布局 -->
        <div class="glass-card p-6 rounded-xl">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-white">License Allocation Records</h3>
                <button class="btn-primary px-4 py-2 rounded-lg text-sm font-semibold">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Generate License
                </button>
            </div>

            <div class="space-y-4">
                <!-- 许可证卡片 1 -->
                <div class="p-4 rounded-lg bg-white/5 hover:bg-white/10 transition">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <div class="text-xs text-white/40 mb-1">License Key</div>
                            <code class="text-xs bg-white/10 px-2 py-1 rounded text-accent-cyan">WK-2024-ABCD-1234</code>
                        </div>
                        <div>
                            <div class="text-xs text-white/40 mb-1">Product</div>
                            <div class="text-sm text-white">GTA V Menu</div>
                        </div>
                        <div>
                            <div class="text-xs text-white/40 mb-1">Assigned To</div>
                            <div class="text-sm text-white/60">JohnDoe</div>
                        </div>
                        <div>
                            <div class="text-xs text-white/40 mb-1">Time</div>
                            <div class="text-sm text-white">Activated: 2024-01-15</div>
                            <div class="text-xs text-white/60">Expires: 2025-01-15</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-white/10">
                        <span class="status-badge status-online text-xs">Active</span>
                        <div class="flex gap-2">
                            <button class="text-white/40 hover:text-accent-purple transition p-2 rounded-lg hover:bg-white/5" title="View Details">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                            <button class="text-white/40 hover:text-red-500 transition p-2 rounded-lg hover:bg-white/5" title="Revoke">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 许可证卡片 2 -->
                <div class="p-4 rounded-lg bg-white/5 hover:bg-white/10 transition">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <div class="text-xs text-white/40 mb-1">License Key</div>
                            <code class="text-xs bg-white/10 px-2 py-1 rounded text-accent-cyan">WK-2024-EFGH-5678</code>
                        </div>
                        <div>
                            <div class="text-xs text-white/40 mb-1">Product</div>
                            <div class="text-sm text-white">RDR2 Mod</div>
                        </div>
                        <div>
                            <div class="text-xs text-white/40 mb-1">Assigned To</div>
                            <div class="text-sm text-white/60">AliceSmith</div>
                        </div>
                        <div>
                            <div class="text-xs text-white/40 mb-1">Time</div>
                            <div class="text-sm text-white">Activated: 2024-02-20</div>
                            <div class="text-xs text-white/60">Expires: 2024-08-20</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-white/10">
                        <span class="status-badge status-online text-xs">Active</span>
                        <div class="flex gap-2">
                            <button class="text-white/40 hover:text-accent-purple transition p-2 rounded-lg hover:bg-white/5" title="View Details">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                            <button class="text-white/40 hover:text-red-500 transition p-2 rounded-lg hover:bg-white/5" title="Revoke">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 许可证卡片 3 -->
                <div class="p-4 rounded-lg bg-white/5 hover:bg-white/10 transition">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <div class="text-xs text-white/40 mb-1">License Key</div>
                            <code class="text-xs bg-white/10 px-2 py-1 rounded text-accent-cyan">WK-2024-IJKL-9012</code>
                        </div>
                        <div>
                            <div class="text-xs text-white/40 mb-1">Product</div>
                            <div class="text-sm text-white">GTA VI Beta</div>
                        </div>
                        <div>
                            <div class="text-xs text-white/40 mb-1">Assigned To</div>
                            <div class="text-sm text-white/60">BobWilson</div>
                        </div>
                        <div>
                            <div class="text-xs text-white/40 mb-1">Time</div>
                            <div class="text-sm text-white">Activated: 2024-03-10</div>
                            <div class="text-xs text-white/60">Expires: 2024-06-10</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-white/10">
                        <span class="status-badge status-updating text-xs">Expired</span>
                        <div class="flex gap-2">
                            <button class="text-white/40 hover:text-accent-purple transition p-2 rounded-lg hover:bg-white/5" title="View Details">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                            <button class="text-white/40 hover:text-red-500 transition p-2 rounded-lg hover:bg-white/5" title="Revoke">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-white/60">Showing 1 to 3 of 3 entries</div>
                <div class="flex gap-2">
                    <button class="px-3 py-1 rounded bg-white/5 text-white/40 cursor-not-allowed">Previous</button>
                    <button class="px-3 py-1 rounded bg-white/5 text-white/40 cursor-not-allowed">Next</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Tab -->
    <div id="settings-tab" class="tab-panel">
        <div class="glass-card p-6 rounded-xl">
            <h3 class="text-lg font-semibold mb-6 text-white">Reseller Information</h3>
            <form class="space-y-6">
                <!-- Logo -->
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">Logo</label>
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center">
                            <svg class="w-10 h-10 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <button type="button" class="btn-primary px-4 py-2 rounded-lg text-sm font-semibold">
                            Upload Logo
                        </button>
                    </div>
                </div>

                <!-- Website Link -->
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">Website Link</label>
                    <input type="url" 
                           class="auth-input w-full px-4 py-2 rounded-lg"
                           placeholder="https://your-website.com"
                           value="https://example-reseller.com">
                </div>

                <!-- Payment Methods -->
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">Supported Payment Methods</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <label class="flex items-center gap-2 p-3 rounded-lg bg-white/5 hover:bg-white/10 cursor-pointer transition">
                            <input type="checkbox" class="auth-checkbox" checked>
                            <span class="text-sm text-white">PayPal</span>
                        </label>
                        <label class="flex items-center gap-2 p-3 rounded-lg bg-white/5 hover:bg-white/10 cursor-pointer transition">
                            <input type="checkbox" class="auth-checkbox" checked>
                            <span class="text-sm text-white">Credit Card</span>
                        </label>
                        <label class="flex items-center gap-2 p-3 rounded-lg bg-white/5 hover:bg-white/10 cursor-pointer transition">
                            <input type="checkbox" class="auth-checkbox">
                            <span class="text-sm text-white">Crypto</span>
                        </label>
                        <label class="flex items-center gap-2 p-3 rounded-lg bg-white/5 hover:bg-white/10 cursor-pointer transition">
                            <input type="checkbox" class="auth-checkbox">
                            <span class="text-sm text-white">Alipay</span>
                        </label>
                        <label class="flex items-center gap-2 p-3 rounded-lg bg-white/5 hover:bg-white/10 cursor-pointer transition">
                            <input type="checkbox" class="auth-checkbox">
                            <span class="text-sm text-white">WeChat Pay</span>
                        </label>
                    </div>
                </div>

                <!-- Contact Info -->
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">Contact Information</label>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs text-white/50 mb-1">Discord</label>
                            <input type="text" 
                                   class="auth-input w-full px-4 py-2 rounded-lg"
                                   placeholder="YourDiscord#1234"
                                   value="ResellerAdmin#0001">
                        </div>
                        <div>
                            <label class="block text-xs text-white/50 mb-1">Telegram</label>
                            <input type="text" 
                                   class="auth-input w-full px-4 py-2 rounded-lg"
                                   placeholder="@yourtelegram"
                                   value="@reseller_support">
                        </div>
                        <div>
                            <label class="block text-xs text-white/50 mb-1">Email</label>
                            <input type="email" 
                                   class="auth-input w-full px-4 py-2 rounded-lg"
                                   placeholder="support@example.com"
                                   value="support@reseller.com">
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">Description</label>
                    <textarea class="auth-input w-full px-4 py-2 rounded-lg h-24 resize-none"
                              placeholder="Tell customers about your reseller service...">Authorized WenKing reseller providing premium game mods and 24/7 support.</textarea>
                </div>

                <!-- Save Button -->
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary px-6 py-2 rounded-lg font-semibold">
                        Save Changes
                    </button>
                    <button type="button" class="px-6 py-2 rounded-lg font-semibold bg-white/5 hover:bg-white/10 text-white/80 transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
