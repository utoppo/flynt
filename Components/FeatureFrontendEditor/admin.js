/* globals wp  */
function init () {
  const searchParams = new URL(window.location).searchParams
  const isEditAction = (searchParams.get('action') === 'edit')
  const isPost = (searchParams.get('post') !== null)
  const isRevision = (searchParams.get('revision') !== null)
  const isIframe = (window.location !== window.parent.location)

  if ((isEditAction && isPost && isIframe) || (isRevision && isIframe)) {
    hideAdminBar()
    setFrontendEditingDataAttributes(document)
    const isBlockEditor = (document.querySelector('.block-editor') !== null)
    isBlockEditor ? initBlockEditor() : initClassicEditor()
  }

  function hideAdminBar () {
    const adminBar = document.getElementById('wpadminbar')
    if (adminBar !== null) {
      adminBar.remove()
    }
  }

  function initBlockEditor () {
    const editor = wp.data.dispatch('core/editor')
    const savePost = editor.savePost
    editor.savePost = () => {
      wp.data.subscribe(() => {
        const isSavingPost = wp.data.select('core/editor').isSavingPost()
        if (isSavingPost) {
          window.parent.postMessage('postIsSavingBlockEditor', '*')
        }
      })
      return savePost()
        .then(() => {
          sendReloadParentPageMessage()
        })
    }
  }

  function initClassicEditor () {
    const buttonAddNew = document.querySelector('.page-title-action')
    buttonAddNew && buttonAddNew.remove()

    const headingEditPage = document.querySelector('.wp-heading-inline')
    headingEditPage && headingEditPage.remove()

    const buttonMoveToTrash = document.querySelector('#delete-action')
    buttonMoveToTrash && buttonMoveToTrash.remove()

    const previewAction = document.getElementById('preview-action')
    previewAction && previewAction.remove()

    const postStuff = document.getElementById('poststuff')
    const publishFooter = document.getElementById('major-publishing-actions')
    if (postStuff && publishFooter) {
      publishFooter.classList.add('major-publishing-actions-hidden')
      postStuff.appendChild(publishFooter)
    }

    const messageSuccess = document.querySelector('#message.updated')
    messageSuccess && sendReloadParentPageMessage()

    const buttonSubmit = document.getElementById('publish')
    buttonSubmit.classList.add('publish-button-sticky')

    buttonSubmit.addEventListener('click', () => {
      sendPostIsSavingMessage()
    })

    prepareLinks()
  }

  function prepareLinks () {
    const links = document.querySelectorAll('a[href]')
    links.forEach((link) => {
      const href = link.getAttribute('href')
      if (href.startsWith('https') || href.startsWith('http')) {
        link.setAttribute('target', '_blank')
      }
    })
  }

  function setFrontendEditingDataAttributes (document) {
    document.documentElement.setAttribute('data-frontend-editing', true)
  }

  function sendPostIsSavingMessage () {
    window.parent.postMessage('postIsSaving', '*')
  }

  function sendReloadParentPageMessage () {
    window.parent.postMessage('reloadParentPage', '*')
  }
}

init()
