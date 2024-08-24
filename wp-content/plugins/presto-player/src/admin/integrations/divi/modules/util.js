export function getProvider(src) {
  const provider = "self-hosted";

  if (src) {
    if (src.includes(".mp3")) {
      return "audio";
    }

    const yt_rx = /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/;
    const has_match_youtube = src.match(yt_rx);

    if (has_match_youtube) {
      return "youtube";
    }

    const vm_rx = /(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([‌​0-9]{6,11})[?]?.*/;
    const has_match_vimeo = src.match(vm_rx);

    if (has_match_vimeo) {
      return "vimeo";
    }

    if (src.indexOf("https://vz-") > -1 && src.indexOf("b-cdn.net") > -1) {
      return "bunny";
    }
  }
  return provider;
}
