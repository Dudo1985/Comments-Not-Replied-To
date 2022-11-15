const markAsReadLink = document.getElementsByClassName('cnrt-mark-as-read');

/**
 * Check if a link with class cnrt-mark-as-read has been clicked
 */
if(markAsReadLink.length > 0){
    for (let i = 0; i < markAsReadLink.length; i++) {
        markAsReadLink[i].addEventListener('click', markAsReadAjax, false);
    }
}

function markAsReadAjax() {
    let commentId        = this.dataset.commentId;
    let nonce            = this.dataset.nonce;
    let spanPendingCount = document.getElementById('cnrt-pending-count');
    let columnSpanIcon   = document.getElementById('cnrt-reply-column-icon-'+commentId);
    let columnSpanText   = document.getElementById('cnrt-reply-column-text-'+commentId);

    const params = {
        action:   'cnrt_mark_as_read',
        comment_id: commentId,
        nonce: nonce
    };

    let paramsBody = [];
    for (let property in params) {
        let encodedKey   = encodeURIComponent(property);
        let encodedValue = encodeURIComponent(params[property]);
        paramsBody.push(encodedKey + "=" + encodedValue);
    }
    paramsBody = paramsBody.join("&");

    const optionsAjaxCall = {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
        },
        body: paramsBody
    };

    fetch(ajaxurl, optionsAjaxCall)
        .then(response => {
            if (response.ok === true) {
                return response.json();
            } else {
                //ajax call failed
                throw new Error('Ajax Call Failed.');
            }
        })
        .then(response => {
            //check if response is an object
            if(typeof response === 'object') {
                return response;
            } else {
                throw new Error('Ajax call failed, wrong response');
            }
        })
        .then(response => {
            //if the resposnse has no the property error, this mean the update_comment_meta had success
            if(response.hasOwnProperty('error') === false) {
                return response;
            } else {
                throw new Error('Ajax call failed, unable to insert comment meta');
            }
        })
        .then(response => {
            //update the span in "Comment Reply column"
            columnSpanIcon.classList.remove('dashicons-welcome-comments');
            columnSpanIcon.classList.add('dashicons-yes-alt');
            columnSpanIcon.setAttribute('style','color:green' );
            columnSpanText.innerText = 'This comment has been marked as read';

            //if the meta _cnrt_missing has been deleted, update the number near "Missing Reply" link
            if(response.hasOwnProperty('meta_deleted') === true && response.meta_deleted === true) {
                return response.meta_deleted
            }
        }).then(response => {
            //update "missing reply" number
            let pendingCount = parseInt(spanPendingCount.textContent);
            pendingCount = pendingCount-1;
            spanPendingCount.innerText = pendingCount.toString();
        })
        .catch((error) => {
            console.info(error);
        })
}