import debounce from 'lodash/debounce'

const instances = new Set()
const imageObserver = new window.IntersectionObserver(function (entries, observer) {
  entries.forEach(function (entry) {
    if (entry.isIntersecting) {
      const image = entry.target
      image.reveal()
      imageObserver.unobserve(image)
    }
  })
}, {
  rootMargin: '200px'
})

class FlyntImage extends window.HTMLImageElement {
  constructor () {
    super()
    this.maxWidth = 0
    instances.add(this)
    imageObserver.observe(this)
    this.aspectRatio = Number(this.dataset.aspectratio)
    // console.log('flyntImage')
  }

  reveal () {
    // console.log('reveal')
    this.isRevealed = true
    const { clientWidth } = this
    if (clientWidth > this.maxWidth) {
      // console.log('reveal replace')
      this.maxWidth = clientWidth
      let newSrc = this.dataset.src.replace('{width}', clientWidth)
      if (this.aspectRatio) {
        newSrc = newSrc.replace('{height}', Math.round(clientWidth / this.aspectRatio))
      }
      this.setAttribute('src', newSrc)
      this.removeAttribute('srcset')
    }
  }
}

function onResize (e) {
  console.log('onResize')
  instances.forEach(image => image.isRevealed && image.reveal())
}

window.addEventListener('resize', debounce(onResize, 1000), { passive: true })

window.customElements.define('flynt-image', FlyntImage, { extends: 'img' })
