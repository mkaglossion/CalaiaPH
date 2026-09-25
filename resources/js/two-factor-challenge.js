const countdown = document.querySelector('[data-two-factor-countdown]')

if (countdown) {
  const expiresAt = Number(countdown.dataset.expiresAt)
  const serverNow = Number(countdown.dataset.serverNow)
  const remainingAtLoad = Math.max(0, expiresAt - serverNow)
  const deadline = Date.now() + (remainingAtLoad * 1000)

  const updateCountdown = () => {
    const remaining = Math.max(0, Math.ceil((deadline - Date.now()) / 1000))
    const minutes = Math.floor(remaining / 60)
    const seconds = String(remaining % 60).padStart(2, '0')

    countdown.textContent = `${String(minutes).padStart(2, '0')}:${seconds}`

    if (remaining === 0) {
      window.location.reload()
    }
  }

  updateCountdown()
  window.setInterval(updateCountdown, 1000)
}
