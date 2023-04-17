define(['core/str'], function(str) {
`use strict`;

  str.get_strings([
      {key: 'close', component: 'community_social'},
      {key: 'errormessage', component: 'community_social'}
  ]).done(function(){});

  const mainBlock = document.querySelector(`#region-main .social`);

  const popup = {

    textHead: ``,
    text: ``,

    show: function () {

      const popup = document.createElement(`div`);
        popup.innerHTML = `
          <div class = "modal_header">
            <div class = "modal_head">${this.textHead}</div>
            <span class = "modal_close"></span>
          </div>
          <div class = "modal_inner"></div>

        `;
        popup.classList.add(`modal`);
      const popupInner = popup.querySelector(`.modal_inner`);

        popupInner.innerHTML = this.text;
        this.remove();
        mainBlock.appendChild(popup);
    },

    error: function (message) {

      if (mainBlock.querySelector(`.modal`)) {
        const errorBlock = document.createElement(`div`);
        errorBlock.classList.add(`modal-error-abs`, `alert`, `alert-warning`);
        errorBlock.innerHTML = `
          <span>${M.util.get_string('errormessage', 'community_social')}</span>
          <button class = "btn btn-error close_popup">${M.util.get_string('close', 'community_social')}</button>
        `;
        mainBlock.querySelector(`.modal`).appendChild(errorBlock);
      }else {
        const popup = document.createElement(`div`);
          popup.innerHTML = `
            <span>${M.util.get_string('errormessage', 'community_social')}</span>
            <button class = "btn btn-error close_popup">${M.util.get_string('close', 'community_social')}</button>
          `;
          popup.classList.add(`modal`, `modal-error`);

          this.remove();
          mainBlock.appendChild(popup);
      }

    },

    remove: function () {
      if(mainBlock.querySelector(`.modal`)) {
        mainBlock.querySelector(`.modal`).remove();
      }
    }

  };

  return popup;

});
