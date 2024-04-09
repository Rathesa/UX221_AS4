class CopyrightYear extends HTMLElement{
    connectedCallback(){
        this.innerHTML = new Date().getFullYear();
    }
}

customElements.define("x-year", CopyrightYear);


class TwoSidedMarket extends HTMLElement {
  connectedCallback() {
    this.innerHTML = `<a href="job-seekers">Job Seekers </a>&nbsp; <a href="job-providers">Job Providers</a>`;
  }
}
customElements.define("x-twosided", TwoSidedMarket);