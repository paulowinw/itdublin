const helpers = {
  setUrlPrivate: () => {
    const separator = window.location.href.indexOf("?") === -1 ? "?" : "&";
    const newurl =
      window.location.href + separator + "presto_video_type=private";
    window.history.pushState({ path: newurl }, "", newurl);
  },
  setUrlPublic: () => {
    const separator = window.location.href.indexOf("?") === -1 ? "?" : "&";
    const newurl =
      window.location.href + separator + "presto_video_type=public";
    window.history.pushState({ path: newurl }, "", newurl);
  },
  unsetUrlParams: () => {
    let removed = removeURLParameters(window.location.href, [
      "presto_video_type",
    ]);
    window.history.pushState({ path: removed }, "", removed);
  },
};

function removeURLParameters(url, parameters = []) {
  const parsedUrl = new URL(url);
  parameters.forEach((param) => {
    parsedUrl.searchParams.delete(param);
  });

  return parsedUrl.href;
}

export default helpers;
