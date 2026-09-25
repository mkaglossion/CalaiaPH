document.querySelectorAll('[data-otp-segmented]').forEach((container) => {
  const input = container.querySelector('[data-otp-input]')
  const cells = Array.from(container.querySelectorAll('[data-otp-cell]'))

  if (!input || cells.length === 0) return

  const render = () => {
    const value = input.value.replace(/\D/g, '').slice(0, cells.length)

    if (input.value !== value) input.value = value

    const activeIndex = Math.min(value.length, cells.length - 1)

    cells.forEach((cell, index) => {
      const digit = value[index] || ''

      cell.textContent = digit
      cell.dataset.filled = digit ? 'true' : 'false'
      cell.dataset.active = document.activeElement === input && index === activeIndex ? 'true' : 'false'
    })
  }

  input.addEventListener('input', render)
  input.addEventListener('focus', render)
  input.addEventListener('blur', render)
  input.addEventListener('change', render)

  render()
})
