const { __, sprintf } = wp.i18n;

export const humanSeconds = (savedSeconds) => {
  const hours = Math.floor(savedSeconds / 60 / 60);
  const minutes = Math.floor(savedSeconds / 60) - hours * 60;
  const seconds = savedSeconds % 60;

  let out = "";
  if (hours) {
    out += sprintf(__("%d hours", "presto-player"), hours) + ", ";
  }
  if (minutes) {
    out += sprintf(__("%d minutes", "presto-player"), minutes) + " ";
  }
  if ((hours || minutes) && seconds) {
    out += __("and", "presto-player") + " ";
  }

  out += sprintf(__("%d seconds", "presto-player"), seconds);

  return out;
};

export const timestamp = (seconds) => {
  seconds = parseInt(seconds || 0) * 1000;
  return new Date(seconds).toISOString().substr(11, 8);
};

/*
 * This function remove the user timezone from new Date()
 * https://stackoverflow.com/a/29774197/1972413
 */
export const convertDateTimeToAbsoluteDate = (dateTime) => {
  const offset = new Date().getTimezoneOffset();
  var date = new Date(dateTime.getTime() - offset * 60 * 1000);
  return date.toISOString().split("T")[0] + "T00:00:00.000Z";
};
