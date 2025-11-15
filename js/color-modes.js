/*!
 * Color mode toggler for Bootstrap's docs (https://getbootstrap.com/)
 * Copyright 2011-2024 The Bootstrap Authors
 * Licensed under the Creative Commons Attribution 3.0 Unported License.
 */

(() => {
  'use strict'

  // Force light theme - no dark mode support
  const setTheme = () => {
    document.documentElement.setAttribute('data-bs-theme', 'light')
    // Clear any stored dark theme preference
    if (localStorage.getItem('theme') === 'dark') {
      localStorage.setItem('theme', 'light')
    }
  }

  setTheme()

  // Ensure light theme on page load
  window.addEventListener('DOMContentLoaded', () => {
    setTheme()
  })

  // Prevent theme changes from system preferences
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    setTheme()
  })
})()
