define(['jquery'], function($) {
    if(!window.location.href.includes('quiz/review')) return

    let scrollpos = localStorage.getItem('quiz_review_scrollpos');
    if (scrollpos) {
        const overFlowInterval = setInterval(() => {
            if($('#page').css('overflow') === "auto") {
                $('#page').scrollTop(scrollpos)
                clearInterval(overFlowInterval)
            }
        }, 300)
    }
    
    window.addEventListener("beforeunload" , function(e) {
        localStorage.setItem('quiz_review_scrollpos', $('#page').scrollTop());
    })
})