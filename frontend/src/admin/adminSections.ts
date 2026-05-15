export type AdminSection = {
  slug: string
  title: string
  submenus?: string[]
  features?: string[]
  notes?: string[]
  menuPath?: string
}

export const ADMIN_SECTIONS: AdminSection[] = [
  {
    slug: 'dashboard-overview',
    title: 'Dashboard Overview',
    menuPath: '/admin/overview',
    submenus: [
      'Main Dashboard',
      'System Analytics',
      'Real-time Activity Monitor',
      'Live Users',
      'Server Status',
      'Revenue Overview',
      'Daily Growth Chart',
    ],
  },
  {
    slug: 'user-management',
    title: 'User Management',
    menuPath: '/admin/users',
    submenus: [
      'All Users',
      'Active Users',
      'Banned Users',
      'Pro Users',
      'VIP Users',
      'User Details',
      'User Activity Logs',
      'Suspicious Users',
      'User Ban / Unban',
    ],
  },
  {
    slug: 'contest-management',
    title: 'Contest Management',
    menuPath: '/admin/contests',
    submenus: [
      'All Contests',
      'Create Contest',
      'Daily Contests',
      'Weekly Contests',
      'Monthly Contests',
      'Tournament Contests',
      'Live Contests',
      'Contest Schedule',
      'Contest Results',
      'Contest Analytics',
    ],
  },
  {
    slug: 'typing-engine-control',
    title: 'Typing Engine Control',
    menuPath: '/admin/content',
    submenus: [
      'Paragraph Library',
      'Create Typing Content',
      'Edit Content',
      'Difficulty Levels',
      'English Texts',
      'Bangla Texts',
      'Programming Texts',
      'Content Approval System',
    ],
  },
  {
    slug: 'leaderboard-system',
    title: 'Leaderboard System',
    menuPath: '/admin/leaderboard',
    submenus: [
      'Global Leaderboard',
      'Country Leaderboard',
      'Daily Leaderboard',
      'Weekly Leaderboard',
      'Monthly Leaderboard',
      'Contest Leaderboard',
      'Top 10 Players',
      'Rank Recalculation Tool',
    ],
  },
  {
    slug: 'subscription-management',
    title: 'Subscription Management',
    menuPath: '/admin/subscriptions',
    submenus: [
      'Subscription Plans',
      'Free Users',
      'Pro Users',
      'VIP Users',
      'Upgrade Requests',
      'Active Subscriptions',
      'Expired Subscriptions',
      'Coupon Management',
    ],
  },
  {
    slug: 'payment-system',
    title: 'Payment System',
    menuPath: '/admin/payments',
    submenus: [
      'All Transactions',
      'Pending Payments',
      'Successful Payments',
      'Failed Payments',
      'Refund Requests',
      'Invoices',
      'Payment Gateways (bKash / Nagad / SSLCommerz)',
    ],
  },
  {
    slug: 'badge-reward-system',
    title: 'Badge & Reward System',
    menuPath: '/admin/badges',
    submenus: ['All Badges', 'Create Badge', 'Assign Badges', 'XP System', 'Level System', 'Seasonal Rewards'],
  },
  {
    slug: 'notification-center',
    title: 'Notification Center',
    menuPath: '/admin/notifications',
    submenus: ['Send Notifications', 'Contest Alerts', 'User Notifications', 'Email Campaigns', 'Push Notifications History'],
  },
  {
    slug: 'analytics-reports',
    title: 'Analytics & Reports',
    menuPath: '/admin/reports',
    submenus: ['User Analytics', 'Contest Performance', 'Typing Statistics', 'Revenue Reports', 'Growth Reports', 'Country-wise Analytics'],
  },
  {
    slug: 'live-system-monitor',
    title: 'Live System Monitor',
    menuPath: '/admin/live',
    submenus: ['Live Contest Monitor', 'Live Leaderboard Viewer', 'Active Sessions', 'Real-time WPM Tracking', 'System Performance Monitor'],
  },
  {
    slug: 'security-center',
    title: 'Security Center',
    menuPath: '/admin/security',
    submenus: ['Security Logs', 'Login Attempts', 'IP Tracking', 'Device Tracking', 'Suspicious Activity Detection', 'Anti-cheat Logs'],
  },
  {
    slug: 'support-system',
    title: 'Support System',
    menuPath: '/admin/support',
    submenus: ['Support Tickets', 'User Complaints', 'Contest Disputes', 'Admin Replies', 'Ticket Status Management'],
  },
  {
    slug: 'cms-management',
    title: 'CMS Management',
    menuPath: '/admin/cms',
    submenus: ['Homepage Settings', 'Blog System', 'FAQ Management', 'Terms & Conditions', 'Privacy Policy', 'Banner Management'],
  },
  {
    slug: 'advertisement-system',
    title: 'Advertisement System',
    menuPath: '/admin/advertisements',
    submenus: ['Ad Banners', 'Sponsored Contests', 'Ad Analytics', 'Ad Placements'],
  },
  {
    slug: 'sponsor-management',
    title: 'Sponsor Management',
    menuPath: '/admin/sponsors',
    submenus: ['Sponsors List', 'Add Sponsor', 'Sponsored Events', 'Brand Campaigns'],
  },
  {
    slug: 'system-settings',
    title: 'System Settings',
    menuPath: '/admin/system',
    submenus: ['General Settings', 'Contest Settings', 'Ranking Settings', 'Notification Settings', 'Theme Settings', 'API Settings'],
  },
  {
    slug: 'admin-management',
    title: 'Admin Management',
    menuPath: '/admin/roles',
    submenus: ['All Admins', 'Create Admin', 'Roles & Permissions', 'Activity Logs'],
  },
  {
    slug: 'api-management',
    title: 'API Management',
    menuPath: '/admin/api-management',
    submenus: ['API Logs', 'API Rate Limits', 'Token Management', 'Webhook Logs'],
  },
  {
    slug: 'backup-maintenance',
    title: 'Backup & Maintenance',
    menuPath: '/admin/backup-maintenance',
    submenus: ['Database Backup', 'Restore System', 'Cache Clear', 'Maintenance Mode'],
  },
  {
    slug: 'system-monitoring',
    title: 'System Monitoring',
    menuPath: '/admin/system-monitoring',
    submenus: ['CPU Usage', 'RAM Usage', 'Queue Jobs', 'Redis Status', 'WebSocket Status'],
  },
]

export function getAdminSection(slug: string) {
  return ADMIN_SECTIONS.find((section) => section.slug === slug)
}

export function getAdminSectionPath(section: AdminSection) {
  return section.menuPath ?? `/admin/section/${section.slug}`
}