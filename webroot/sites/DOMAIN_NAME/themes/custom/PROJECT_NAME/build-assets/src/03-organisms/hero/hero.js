let player;

const loadYoutubeIframeSrc = () => {
  if (document.getElementById('youtube-player')) {
    return;
  }
  const script = document.createElement('script');
  script.id = 'youtube-player';
  script.src = '//www.youtube.com/player_api';
  const lastScriptTag = document.getElementsByTagName('script')[document.getElementsByTagName('script').length - 1];
  lastScriptTag.parentNode.insertBefore(script, lastScriptTag);
};

function onPlayerReady(event) {
  event.target.playVideo();
}

function onPlayerStateChange(event) {
  if (event.data === 0) {
    event.target.seekTo(0);
  }
}

window.onYouTubePlayerAPIReady = function onYouTubePlayerAPIReady() {
  const heros = document.querySelectorAll('.js-hero');
  if (heros.length === 0) {
    return;
  }

  for (let i = 0; i < heros.length; i += 1) {
    const currentHero = heros[i];
    const videoData = currentHero.dataset.video;
    if (videoData) {
      const { id } = JSON.parse(videoData);
      player = new window.YT.Player(`player_${id}`, {
        events: {
          onReady: onPlayerReady,
          onStateChange: onPlayerStateChange,
        },
      });
    }
  }
};

Drupal.behaviors.hero = {
  attach(context) {
    const heros = document.querySelectorAll('.js-hero:not(.loaded)');
    if (heros.length === 0) {
      return;
    }

    for (let i = 0; i < heros.length; i += 1) {
      const currentHero = heros[i];
      const iframeWrapper = currentHero.querySelector('.js-hero-iframe-wrapper');
      const videoData = currentHero.dataset.video;
      currentHero.classList.add('loaded');

      if (videoData) {
        const { id, oEmbed } = JSON.parse(videoData);

        if (oEmbed !== 'null' && oEmbed !== null) {
          let isYoutube = false;
          if (oEmbed.html.indexOf('iframe') > -1) {
            const regex = /<iframe.*?src="(.*?)"/;
            const oldSrc = regex.exec(oEmbed.html)[1];
            let newSrc = oldSrc;
            if (oldSrc.indexOf('youtube') > -1) {
              isYoutube = true;
              const { origin } = window.location;
              newSrc = `${oldSrc}&mute=1&controls=0&showinfo=0&autohide=1&background=1&playsinline=1&origin=${origin}&enablejsapi=1`; // We need to set this ourselves, otherwise we are not sure it is gonna play.
              oEmbed.html = oEmbed.html.replace('<iframe', `<iframe id="player_${id}"`);
            } else {
              newSrc = `${oldSrc}&autoplay=1&mute=1&controls=0&showinfo=0&autohide=1&background=1`; // We need to set this ourselves, otherwise we are not sure it is gonna play.
            }
            oEmbed.html = oEmbed.html.replace(oldSrc, newSrc);
          } else if (oEmbed.html.indexOf('video') > -1) {
            oEmbed.html = oEmbed.html.replace('<video', '<video autoplay loop muted');
          }
          iframeWrapper.innerHTML = oEmbed.html;
          if (isYoutube) {
            loadYoutubeIframeSrc();
          }
        }
      }
    }
  },
};
