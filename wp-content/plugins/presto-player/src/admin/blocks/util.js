const { useRef, useEffect } = wp.element;

export function usePrevious(value) {
  const ref = useRef();
  useEffect(() => {
    ref.current = value;
  });
  return ref.current;
}

export function snackbarNotice({ status = "success", message }) {
  wp.data.dispatch("core/notices").createNotice(
    status, // Can be one of: success, info, warning, error.
    message, // Text string to display.
    { type: "snackbar" }
  );
}

export const bytesToSize = (bytes) => {
  var sizes = ["Bytes", "KB", "MB", "GB", "TB"];
  if (bytes == 0) return "0 Byte";
  var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
  return Math.round(bytes / Math.pow(1024, i), 2) + " " + sizes[i];
};

export const toDate = (d) => {
  d = new Date(d);
  var hours = d.getHours();
  var minutes = d.getMinutes();
  var ampm = hours >= 12 ? "pm" : "am";
  hours = hours % 12;
  hours = hours ? hours : 12;
  minutes = minutes < 10 ? "0" + minutes : minutes;

  return (
    d.getDate() +
    "-" +
    (d.getMonth() + 1) +
    "-" +
    d.getFullYear() +
    " at " +
    hours +
    ":" +
    minutes +
    ampm
  );
};

export function timeToSeconds(time) {
  let pieces = time.split(":");
  let seconds;
  if (pieces.length > 1) {
    seconds = parseInt(pieces[0]) * 60;
  }
  return parseInt(pieces[1]) + parseInt(seconds);
}

export function secondsToTime(number) {
  let seconds = parseInt(number, 10);
  let minutes = Math.floor(seconds / 60);
  if (seconds < 10) {
    seconds = "0" + seconds;
  }
  return minutes + ":" + seconds;
}

export function sanitizeTime(time) {
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

export function getProvider(src) {
  const provider = "self-hosted";

  if (src) {
    const yt_rx =
      /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/;
    const has_match_youtube = src.match(yt_rx);

    if (has_match_youtube) {
      return "youtube";
    }

    const vm_rx =
      /(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([‌​0-9]{6,11})[?]?.*/;
    const has_match_vimeo = src.match(vm_rx);

    if (has_match_vimeo) {
      return "vimeo";
    }

    if (src.indexOf("https://vz-") > -1 && src.indexOf("b-cdn.net") > -1) {
      return "bunny";
    }

    if (src.indexOf(".mp3") > -1) {
      return "audio";
    }
  }
  return provider;
}

