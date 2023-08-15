define(['format_picturelink/ajax'], function (ajax) {
    `use strict`

    const getAllCoords = () => {

        const items = Array.from(document.querySelectorAll(`.picturelink_item`));
        let allCoords = [];
        let itemCoords = {};

        items.forEach((item) => {
            itemCoords = {
                id: item.dataset.id,
                coordx: item.dataset.coordx,
                coordy: item.dataset.coordy
            };
            allCoords.push(itemCoords);
        });
        allCoords = JSON.stringify(allCoords);
        return allCoords;
    }

    function getCoords(elem) {
        return {
            top: elem.offsetTop,
            left: elem.offsetLeft
        };
    }

    const dragBall = (e, ball) => {

        ball.style.transition = 0 + 's';

        const coords = getCoords(ball);
        let shiftX = e.pageX - coords.left;
        let shiftY = e.pageY - coords.top;

        const moveAt = (e) => {
            let parentWidth = ball.parentNode.offsetWidth;
            let parentHeight = ball.parentNode.offsetHeight;

            ball.style.left = (getCoords(ball).left < 0) ? 0 + '%' :  (e.pageX - shiftX) / parentWidth * 100 + '%';
            ball.style.top = (getCoords(ball).top < 0) ? 0 + '%' : (e.pageY - shiftY) / parentHeight * 100 + '%';

            if (getCoords(ball).left > ball.parentNode.offsetWidth) {
                ball.style.left = '100%';
            }
            if (getCoords(ball).top > ball.parentNode.offsetHeight) {
                ball.style.top = '100%';
            }
        }

        ball.ondragstart = function () {
            return false;
        };

        ball.parentNode.onmousemove = function (e) {
            moveAt(e);
        };

        ball.onmouseup = function () {
            ball.parentNode.onmousemove = null;
            ball.onmouseup = null;

            ball.dataset.coordx = ball.style.left.replace(/\%/, '');
            ball.dataset.coordy = ball.style.top.replace(/\%/, '');

            ajax.method = `rewriteactivitiescoords`;
            ajax.data = {};
            ajax.data.coords = getAllCoords();
            ajax.send();
        };
    }

    return dragBall;

});
