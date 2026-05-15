export type AdminSection = {
  slug: string
  title: string
  submenus?: string[]
  features?: string[]
  notes?: string[]
}

export const ADMIN_SECTIONS: AdminSection[] = [
  {
    slug: 'users',
    title: 'Users',
    submenus: [
      'All Users',
      'Free Users',
      'Pro Users',
      'VIP Users',
      'Banned Users',
      'Online Users',
      'User Reports',
      'User Activity Logs',
    ],
    features: [
      'Search users',
      'Ban/unban',
      'Reset password',
      'View profile',
      'Contest history',
      'Suspicious activity review',
    ],
  },
  {
    slug: 'contests',
    title: 'Contests',
    submenus: [
      'All Contests',
      'Daily Contests',
      'Weekly Contests',
      'Monthly Contests',
      'Tournament Events',
      'Create Contest',
      'Contest Categories',
      'Contest Results',
    ],
    features: ['Create contests', 'Edit contests', 'Cancel contests', 'Clone contests', 'Schedule contests'],
    notes: ['Open /admin/contests for full contest lifecycle operations.'],
  },
  {
    slug: 'live-matches',
    title: 'Live Matches',
    features: ['Live participants', 'Live WPM', 'Real-time leaderboard', 'Active sessions', 'Contest monitoring'],
  },
  {
    slug: 'leaderboards',
    title: 'Leaderboards',
    submenus: [
      'Global Rankings',
      'Country Rankings',
      'Daily Rankings',
      'Weekly Rankings',
      'Monthly Rankings',
      'Top 10 Players',
    ],
    features: ['Reset rankings', 'Recalculate score', 'Remove fake scores'],
  },
  {
    slug: 'subscriptions',
    title: 'Subscriptions',
    submenus: ['Plans', 'User Subscriptions', 'Active Plans', 'Expired Plans', 'Upgrade Requests', 'Coupons & Discounts'],
    features: ['Create packages', 'Manage plans', 'Upgrade users', 'Cancel subscriptions'],
  },
  {
    slug: 'payments',
    title: 'Payments',
    submenus: ['Payment History', 'Pending Payments', 'Withdraw Requests', 'Refund Requests', 'Payment Gateways', 'Invoices'],
    features: ['Verify transactions', 'Manage gateways', 'Revenue tracking'],
  },
  {
    slug: 'badges-rewards',
    title: 'Badges & Rewards',
    submenus: ['All Badges', 'Create Badge', 'Reward Points', 'XP Levels', 'Season Rewards'],
    features: ['Assign badges', 'Configure rewards', 'XP rules'],
  },
  {
    slug: 'reports-analytics',
    title: 'Reports & Analytics',
    submenus: ['User Analytics', 'Contest Analytics', 'Revenue Reports', 'Traffic Reports', 'Typing Statistics', 'Growth Reports'],
    features: ['Daily users graph', 'Revenue chart', 'Contest engagement graph', 'User retention graph'],
  },
  {
    slug: 'typing-content',
    title: 'Typing Content',
    submenus: ['Paragraph Library', 'Add Paragraph', 'Categories', 'Programming Texts', 'English Texts', 'Bangla Texts'],
    features: ['Upload content', 'Approve/reject text', 'Difficulty level management'],
  },
  {
    slug: 'notifications',
    title: 'Notifications',
    submenus: ['Push Notifications', 'Email Notifications', 'Contest Alerts', 'Announcements'],
    features: ['Send broadcast', 'Schedule notifications'],
  },
  {
    slug: 'support-tickets',
    title: 'Support Tickets',
    submenus: ['Open Tickets', 'Pending Tickets', 'Closed Tickets', 'Reported Users'],
  },
  {
    slug: 'advertisements',
    title: 'Advertisements',
    features: ['Banner ads', 'Sponsored contests', 'Ad placements', 'Ad analytics'],
  },
  {
    slug: 'sponsors',
    title: 'Sponsors',
    features: ['Manage sponsors', 'Sponsor campaigns', 'Sponsored tournaments'],
  },
  {
    slug: 'cms-management',
    title: 'CMS Management',
    submenus: ['Homepage', 'Blogs', 'FAQs', 'Terms & Conditions', 'Privacy Policy', 'Banners'],
  },
  {
    slug: 'settings',
    title: 'Settings',
    submenus: ['General Settings', 'Contest Settings', 'Ranking Settings', 'Notification Settings', 'Theme Settings', 'Localization'],
  },
  {
    slug: 'admins-roles',
    title: 'Admins & Roles',
    submenus: ['All Admins', 'Roles', 'Permissions', 'Create Admin'],
    features: ['Super Admin', 'Contest Admin', 'Moderator', 'Support Admin', 'Content Manager'],
  },
  {
    slug: 'activity-logs',
    title: 'Activity Logs',
    features: ['Track admin actions', 'Track contest edits', 'Track user bans', 'Track payment updates'],
  },
  {
    slug: 'system-monitoring',
    title: 'System Monitoring',
    features: ['CPU usage', 'RAM usage', 'Redis monitoring', 'Queue monitoring', 'WebSocket status'],
  },
  {
    slug: 'api-management',
    title: 'API Management',
    features: ['API logs', 'API rate limits', 'Token management', 'Failed requests'],
  },
  {
    slug: 'security-center',
    title: 'Security Center',
    features: ['Suspicious login detection', 'Cheat detection', 'IP banning', 'Device tracking', 'Firewall settings'],
  },
  {
    slug: 'backup-maintenance',
    title: 'Backup & Maintenance',
    features: ['Database backup', 'System restore', 'Maintenance mode', 'Cache clearing'],
  },
]

export function getAdminSection(slug: string) {
  return ADMIN_SECTIONS.find((section) => section.slug === slug)
}