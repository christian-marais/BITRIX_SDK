async function setBitrix24Slider(querySelector, url,param='') {
        let link = document.querySelector(querySelector);
        link.innerText='Voir';
    try {
        b24=await B24Js.initializeB24Frame();
        link.addEventListener('click', function() {
            try {
                    b24.slider.openPath(
                    b24.slider.getUrl(url),
                    950
                );
                
            } catch (error) {
                console.error(error);
            }
        });

        console.log(b24);
    } catch (error) {
        link.addEventListener('click', function() {
            window.location.href = param;
        })
        console.error(error);
    }
}