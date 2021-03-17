const playVideo = (oEmbed, parent, target, mute) => {
  if (oEmbed !== 'null' && oEmbed !== null) {
    const newTarget = target;
    let oEmbedCode = oEmbed.html;
    const muteText = mute === 1 ? 'muted' : '';

    if (oEmbed.html.indexOf('iframe') > -1) {
      oEmbedCode = oEmbedCode.replace('mute=1', '');
      oEmbedCode = oEmbedCode.replace('controls=0', '');
      oEmbedCode = oEmbedCode.replace('showinfo=0', '');
      const regex = /<iframe.*?src="(.*?)"/;
      const oldSrc = regex.exec(oEmbedCode)[1];
      const newSrc = `${oldSrc}&autoplay=1&showinfo=0&autohide=1&mute=${mute}`; // We need to set this ourselves, otherwise we are not sure it is gonna play.
      oEmbedCode = oEmbedCode.replace(oldSrc, newSrc);
    } else if (oEmbedCode.indexOf('video') > -1) {
      oEmbedCode = oEmbedCode.replace('<video', `<video autoplay ${muteText}`);
    }

    parent.classList.add('video--hide-content');
    newTarget.innerHTML = oEmbedCode;
  }
};

Drupal.behaviors.video = {
  attach(context) {
    const videos = document.querySelectorAll('.js-video:not(.loaded)');
    if (videos.length === 0) {
      return;
    }

    for (let i = 0; i < videos.length; i += 1) {
      const currentVideo = videos[i];
      const playIcon = currentVideo.querySelector('.js-video-play-icon');
      const iframeWrapper = currentVideo.querySelector('.js-video-iframe-wrapper');
      const videoData = currentVideo.dataset.video;
      const { oEmbed } = JSON.parse(videoData);
      const autoPlay = currentVideo.dataset.autoplay.toLowerCase();
      let isPlaying = false;
      currentVideo.classList.add('loaded');

      if (autoPlay === 'true') {
        document.addEventListener('scroll', () => {
          const currentVideoRect = currentVideo.getBoundingClientRect();
          const bodyRect = document.body.getBoundingClientRect();
          const videoTop = currentVideoRect.top;
          const vLeft = currentVideoRect.left;
          const vBottom = currentVideoRect.bottom;
          const videoRight = currentVideoRect.right;
          const bodyWidth = bodyRect.clientWidth;
          const bodyHeight = bodyRect.clientHeight;
          const winHeight = window.innerHeight;
          const winWidth = window.innerWidth;
          const visibleX = videoTop >= 0 && vLeft >= 0 && vBottom <= (winHeight || bodyHeight);
          const visibleY = videoRight <= (winWidth || bodyWidth);
          const isVisible = visibleX && visibleY;

          if (isVisible && !isPlaying) {
            isPlaying = true;
            playVideo(oEmbed, currentVideo, iframeWrapper, 1);
          }
        });
      } else {
        playIcon.addEventListener('click', (e) => {
          playVideo(oEmbed, e.currentTarget.parentNode, iframeWrapper, 0);
          isPlaying = true;
        });
      }
    }
  },
};
