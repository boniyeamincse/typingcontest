import { useEffect, useState } from 'react'
import type { AuthUser } from '../api/http'
import './ProfileSummary.css'

type ProfileSummaryProps = {
  user: AuthUser | null
}

export function ProfileSummary({ user }: ProfileSummaryProps) {
  const [progress, setProgress] = useState(0)

  const xp = user?.xp_points ?? 0
  const level = Math.floor(xp / 1000) + 1
  const currentXpInLevel = xp % 1000
  const progressPercent = (currentXpInLevel / 1000) * 100

  useEffect(() => {
    // Animate progress bar on mount
    const timer = setTimeout(() => {
      setProgress(progressPercent)
    }, 100)
    return () => clearTimeout(timer)
  }, [progressPercent])

  if (!user) return null

  return (
    <div className="surface-card profile-summary">
      <div className="profile-avatar-wrap">
        <div className="profile-avatar">
          {user.avatar ? (
            <img src={user.avatar} alt={user.name} />
          ) : (
            user.name.charAt(0).toUpperCase()
          )}
        </div>
        <div className="profile-badge">{user.plan_type ?? 'Free'}</div>
      </div>

      <div className="profile-info">
        <div className="profile-header">
          <h2>{user.name}</h2>
          <div className="profile-meta">
            <span className="username">@{user.username}</span>
            <span className="country">
              {user.country ? getFlagEmoji(user.country) : '🌐'} {user.country ?? 'Global'}
            </span>
          </div>
        </div>

        <div className="profile-stats-container">
          <div className="profile-stat-item">
            <span className="profile-stat-label">Global Rank</span>
            <span className="profile-stat-value">#{user.global_rank ?? '---'}</span>
          </div>
          <div className="profile-stat-item">
            <span className="profile-stat-label">Avg. Speed</span>
            <span className="profile-stat-value">{user.total_wpm ?? 0} WPM</span>
          </div>
          <div className="profile-stat-item">
            <span className="profile-stat-label">Accuracy</span>
            <span className="profile-stat-value">{user.accuracy_avg ?? 0}%</span>
          </div>
          <div className="profile-stat-item">
            <span className="profile-stat-label">Total XP</span>
            <span className="profile-stat-value">{xp.toLocaleString()}</span>
          </div>
        </div>

        <div className="profile-xp-section">
          <div className="profile-xp-bar-wrap">
            <div 
              className="profile-xp-bar-fill" 
              style={{ width: `${progress}%` }}
            />
          </div>
          <div className="profile-level-tag">
            <span>Level {level}</span>
            <span>{currentXpInLevel} / 1000 XP to Level {level + 1}</span>
          </div>
        </div>
      </div>
    </div>
  )
}

function getFlagEmoji(countryCode: string) {
  const codePoints = countryCode
    .toUpperCase()
    .split('')
    .map((char) => 127397 + char.charCodeAt(0))
  return String.fromCodePoint(...codePoints)
}
