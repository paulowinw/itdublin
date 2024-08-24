// sign a private url
export const signURL = async (src) => {
  if (!src) {
    return;
  }

  const preview = await wp.apiFetch({
    path: "presto-player/v1/bunny/sign",
    method: "POST",
    data: {
      url: src,
      id: 0, // backwards compat
    },
  });

  return preview;
};
