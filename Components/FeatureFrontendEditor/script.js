/* global MutationObserver, DOMParser */
import { buildRefs } from '@/assets/scripts/helpers.js'

export default function (el) {
  const refs = buildRefs(el)
  const searchParams = new URL(document.location).searchParams
  const isSidebarOpen = (searchParams.get('frontendEditorVisible') === 'true')
  document.documentElement.setAttribute('data-sidebar-visible', isSidebarOpen)

  const frontendEditAdminBarButton = getAdminbarButton(refs, isSidebarOpen)
  frontendEditAdminBarButton.addEventListener('click', toggleSidebar)

  window.addEventListener('resize', setDocumentElementMargin, { passive: true })
  initSidebarResizeMutationObserver()

  function toggleSidebar () {
    const isAriaHidden = (refs.editSidebar.getAttribute('aria-hidden') === 'true')

    refs.editSidebar.setAttribute('aria-hidden', !isAriaHidden)
    document.documentElement.setAttribute('data-sidebar-visible', isAriaHidden)
    frontendEditAdminBarButton.setAttribute('aria-expanded', isAriaHidden)

    const url = new URL(window.location.href)
    if (isAriaHidden) {
      setDocumentElementMargin()
      url.searchParams.set('frontendEditorVisible', isAriaHidden)
      window.history.replaceState({}, '', url)
      el.focus()
    } else {
      resetDocumentElementMargin()
      url.searchParams.delete('frontendEditorVisible')
      window.history.replaceState({}, '', url)
    }
  }

  function initSidebarResizeMutationObserver () {
    const observer = new MutationObserver(() => {
      setDocumentElementMargin()
    })

    observer.observe(refs.editSidebar, {
      attributes: true,
      attributeFilter: ['style']
    })
  }

  function setDocumentElementMargin () {
    const isDesktopMediaQuery = window.matchMedia('(min-width: 1024px)')
    if (isDesktopMediaQuery.matches) {
      const newWidth = refs.editSidebar.offsetWidth
      document.documentElement.style.marginInlineStart = `min(${newWidth}px, 100%)`
    } else {
      resetDocumentElementMargin()
    }
  }

  function resetDocumentElementMargin () {
    document.documentElement.style.removeProperty('margin-inline-start')
  }

  function getAdminbarButton (refs, isSidebarOpen) {
    const frontendEditAdminBarButton = document.querySelector('#wpadminbar #wp-admin-bar-frontend-editing > .ab-item')
    const newButton = document.createElement('button')
    newButton.innerHTML = frontendEditAdminBarButton.innerHTML
    newButton.className = frontendEditAdminBarButton.className
    if (refs.editSidebar) {
      newButton.setAttribute('aria-controls', refs.editSidebar.id)
    }
    newButton.setAttribute('aria-expanded', isSidebarOpen)

    frontendEditAdminBarButton.replaceWith(newButton)

    return document.querySelector('#wpadminbar #wp-admin-bar-frontend-editing > .ab-item')
  }

  refs.iFrame.addEventListener('load', () => {
    el.setAttribute('data-is-iframe-loading', 'false')
  }, { once: true })

  window.addEventListener('message', (event) => {
    if (event.data === 'postIsSaving') {
      el.setAttribute('data-is-iframe-loading', 'true')
      postIsSaving()
    }

    if (event.data === 'postIsSavingBlockEditor') {
      postIsSaving()
    }

    if (event.data === 'reloadParentPage') {
      document.documentElement.setAttribute('data-post-is-saving', 'false')
      const url = new URL(window.location.href)
      fetch(url)
        .then((res) => res.text())
        .then((text) => replaceDynamicContent(text))
        .then(() => {
          const iFramePageYOffset = parseInt(document.documentElement.getAttribute('data-iframe-page-y-offset'))
          refs.iFrame.contentWindow.scrollTo(0, iFramePageYOffset)
          el.setAttribute('data-is-iframe-loading', 'false')
        })
    }

    if (event.data === 'frontendEditingIFrameIsLoaded') {
      el.setAttribute('data-is-iframe-loading', 'false')
    }
  })

  function postIsSaving () {
    document.documentElement.setAttribute('data-post-is-saving', 'true')
    const iFramePageYOffset = parseInt(refs.iFrame.contentWindow.scrollY)
    document.documentElement.setAttribute('data-iframe-page-y-offset', iFramePageYOffset)
  }

  async function replaceDynamicContent (text) {
    if (!text) return

    const currentNodes = document.querySelector('.mainContent')
    const parser = new DOMParser()
    const futureHtml = parser.parseFromString(text, 'text/html')
    const futureNodes = futureHtml.querySelector('.mainContent')
    currentNodes.replaceChildren(...futureNodes.children)
  }
}
