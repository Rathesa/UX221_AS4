class CopyrightYear extends HTMLElement{
    connectedCallback(){
        this.innerHTML = new Date().getFullYear();
    }
}

customElements.define("x-year", CopyrightYear);
class TwoSidedMarket extends HTMLElement {
  connectedCallback() {
    this.innerHTML = `<a href="sewing-guides">Sewing Guides</a>&nbsp; <a href="products">Products</a>`;
  }
}
customElements.define("x-twosided", TwoSidedMarket);