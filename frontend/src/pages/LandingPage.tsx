import { Link } from 'react-router-dom'
import { useAuth } from '../auth/AuthContext'
import './LandingPage.css'

export function LandingPage() {
  const { isAuthenticated } = useAuth()

  return (
    <div className="landing-page">
      <header className="shell-topbar">
        <div className="shell-brand">
          <span className="shell-brand__dot" aria-hidden="true" />
          TypingContest
        </div>
        <nav className="shell-nav">
          <Link to="/contests" className="shell-nav__link">Contests</Link>
          <Link to="/leaderboard" className="shell-nav__link">Global Ranks</Link>
        </nav>
        <div className="shell-user">
          {isAuthenticated ? (
            <Link to="/dashboard" className="btn-secondary">Go to Dashboard</Link>
          ) : (
            <div style={{ display: 'flex', gap: '0.5rem' }}>
              <Link to="/login" className="shell-nav__link">Sign In</Link>
              <Link to="/register" className="btn-primary" style={{ padding: '0.4rem 1.2rem', borderRadius: '999px' }}>Join Free</Link>
            </div>
          )}
        </div>
      </header>

      <main>
        <section className="hero-section">
          <div className="hero-container">
            <div className="hero-content">
              <span className="hero-tagline">Now in Beta: Season 1 is Live</span>
              <h1 className="hero-title">
                Master Your Speed.<br />
                <span>Conquer the Arena.</span>
              </h1>
              <p className="hero-subtitle">
                Join the ultimate competitive typing experience. Compete in daily contests, 
                climb the global leaderboard, and prove you're the fastest typist.
              </p>
              <div className="hero-ctas">
                <Link to={isAuthenticated ? "/contests" : "/register"} className="btn-primary hero-btn-primary">
                  {isAuthenticated ? "Enter Arena" : "Get Started Now"}
                </Link>
                <Link to="/contests" className="btn-secondary hero-btn-secondary">
                  Explore Contests
                </Link>
              </div>
            </div>
            <div className="hero-visual">
              <div className="hero-image-wrap">
                <img src="/images/hero-image.png" alt="Futuristic Typing Arena" />
                <div className="image-glow" />
              </div>
            </div>
          </div>

          <div className="stats-preview">
            <div className="stat-item">
              <span className="stat-number">250K+</span>
              <span className="stat-label">Typists</span>
            </div>
            <div className="stat-item">
              <span className="stat-number">1.2M</span>
              <span className="stat-label">Contests Run</span>
            </div>
            <div className="stat-item">
              <span className="stat-number">180+</span>
              <span className="stat-label">Countries</span>
            </div>
          </div>
        </section>

        <section className="features-section">
          <div className="section-header">
            <h2 className="section-title">Everything you need to level up.</h2>
            <p style={{ color: 'var(--muted)' }}>Precision-engineered tools for serious typists.</p>
          </div>

          <div className="features-grid">
            <article className="feature-card">
              <div className="feature-icon">⚡</div>
              <h3>Live Contests</h3>
              <p>Compete in real-time against other players in high-stakes typing battles with unique passages.</p>
            </article>

            <article className="feature-card">
              <div className="feature-icon">📊</div>
              <h3>Deep Analytics</h3>
              <p>Get detailed reports on your WPM, accuracy, consistency, and even keystroke-level timing data.</p>
            </article>

            <article className="feature-card">
              <div className="feature-icon">🏆</div>
              <h3>Global Ranking</h3>
              <p>See where you stand against the world's best with our ELO-based ranking system and regional boards.</p>
            </article>

            <article className="feature-card">
              <div className="feature-icon">🏅</div>
              <h3>Earn Badges</h3>
              <p>Unlock exclusive achievements and showcase your skills with profile badges and custom avatars.</p>
            </article>

            <article className="feature-card">
              <div className="feature-icon">💎</div>
              <h3>Pro Tier</h3>
              <p>Unlock unlimited contest history, advanced training modes, and ad-free experience with TypingContest Pro.</p>
            </article>

            <article className="feature-card">
              <div className="feature-icon">🌐</div>
              <h3>API Access</h3>
              <p>Build your own tools or integrate your typing stats anywhere with our developer-friendly API.</p>
            </article>
          </div>
        </section>
      </main>

      <footer className="landing-footer">
        <span className="footer-logo">TypingContest</span>
        <div style={{ display: 'flex', justifyContent: 'center', gap: '2rem', marginBottom: '2rem' }}>
          <Link to="/about" className="muted-link">About</Link>
          <Link to="/terms" className="muted-link">Terms</Link>
          <Link to="/privacy" className="muted-link">Privacy</Link>
          <Link to="/contact" className="muted-link">Contact</Link>
        </div>
        <p className="copyright">&copy; 2026 TypingContest. All rights reserved.</p>
      </footer>
    </div>
  )
}
