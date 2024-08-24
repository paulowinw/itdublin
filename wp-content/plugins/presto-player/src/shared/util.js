export function getYoutubeId(url) {
  const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
  const match = (url || "").match(regExp);
  return match && match?.[2]?.length === 11 ? match[2] : null;
}

export function getVimeoId(url) {
  const regExp =
    /^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/;
  const parseUrl = regExp.exec(url || "");
  return parseUrl?.[5] || "";
}

export function saveAttachment({ video_id, src, type }) {
  return new Promise((resolve, reject) => {
    jQuery.post(
      prestoPlayer.ajaxurl,
      {
        action: "presto_save_external_attachment",
        _wpnonce: prestoPlayer.nonce,
        post_id: wp.data.select("core/editor").getCurrentPostId(),
        video_id,
        src,
        type,
      },
      function ({ data }) {
        resolve(data);
      }
    );
  });
}

export function determineVideoUrlType(url) {
  const youtube_id = getVimeoId(url);
  if (youtube_id) {
    return {
      video_id: youtube_id,
      type: "vimeo",
    };
  }

  const vimeo_id = getYoutubeId(url);
  if (vimeo_id) {
    return {
      video_id: vimeo_id,
      type: "youtube",
    };
  }

  return {
    $video_id: 0,
    $type: "none",
  };
}

export function convertChapter(chapter) {
  let draft = time;
  // remove any letters
  draft = draft.replace(/[^\d\d:\d\d.-]/g, "");
  // make sure we have :
  if (!draft.includes(":")) {
    return `${draft}:00`;
  }

  // must have something before :00
  if (draft.substr(0, draft.indexOf(":")).length === 0) {
    draft = `0${draft}`;
  }

  // only allow 2 characters after :
  let index = draft.indexOf(":");
  draft = draft.substring(0, index + 3);
  return draft;
}

// get file extension
export function getFileExtension(url) {
  return url.split(/[#?]/)[0].split(".").pop().trim();
}

// is the source hls?
export function isHLS(url) {
  return typeof url === "string" && url.includes(".m3u8");
}

export const convertHex = (hexCode, opacity = 1) => {
  var hex = hexCode.replace("#", "");

  if (hex.length === 3) {
    hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
  }

  var r = parseInt(hex.substring(0, 2), 16),
    g = parseInt(hex.substring(2, 4), 16),
    b = parseInt(hex.substring(4, 6), 16);

  /* Backward compatibility for whole number based opacity values. */
  if (opacity > 1 && opacity <= 100) {
    opacity = opacity / 100;
  }

  return "rgba(" + r + "," + g + "," + b + "," + opacity + ")";
};
