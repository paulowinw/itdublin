/**
 * Is the video playable
 */
export const isPlayable = (video) =>
  video?.status == 3 && video?.availableResolutions.length;

/**
 * It's selectable if it's playable or live;
 */
export const isSelectable = (video) => isPlayable(video) || video?.status === 4;

/**
 * Get the status text
 * @returns string
 */
export const getStatusText = (video) => {
  if (video?.status == 0) return "Processing";
  if (video?.status == 1) return "Uploaded";
  if (video?.status == 2) return "Processing";
  if (isPlayable(video)) return "Playable";
  if (video?.status == 3) return "Encoding";
  if (video?.status == 4) return "Live";
  if (video?.status == 5) return "Error";
  if (video?.status == 6) return "Upload Failed";
};

export const getLengthToTime = (length) => {
  if (length == undefined || length == null) return "";
  return new Date(length * 1000).toISOString().substr(11, 8);
};

export const bytesToSize = (bytes) => {
  if (bytes == undefined || bytes == 0) return "Unknown";
  var sizes = ["b", "KB", "MB", "GB", "TB"];
  if (bytes == 0) return "0 b";
  var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)).toString());
  return (bytes / Math.pow(1024, i)).toFixed(2) + " " + sizes[i];
};

export const stampToDate = (d) => {
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
