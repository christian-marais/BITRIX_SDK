function setBitrix24Slider(querySelector, url) {
    document.addEventListener('DOMContentLoaded', async () => {
        try {
            let $b24 = await B24Js.initializeB24Frame();
            let activitiesLinks = document.querySelectorAll(querySelector);
            activitiesLinks.forEach(link => {
                link.addEventListener('click', async function() {
                    try {
                        await $b24.slider.openPath(
                            $b24.slider.getUrl(url),
                            950
                        );
                    } catch (error) {
                        console.error(error);
                    }
                });
            });

            console.log($b24);
        } catch (error) {
            console.error(error);
        }
    });
}