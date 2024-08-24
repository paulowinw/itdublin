const { apiFetch } = wp;

export default {
  FETCH_FROM_API(action) {
    return apiFetch({
      url: prestoPlayer.root + prestoPlayer.prestoVersionString + action.path,
    });
  },
  async FETCH_FROM_WP_API({ args, path = "" }) {
    let res = await apiFetch({
      path: wp.url.addQueryArgs(`wp/v2/${path}`, args?.query),
      ...(args?.data ? { data: args.data } : {}),
      ...args?.options,
      parse: false,
    });

    const data = await res.json();
    const total = res.headers && res.headers.get("X-WP-Total");
    const total_pages = res.headers && res.headers.get("X-WP-TotalPages");

    return new Promise((resolve, reject) => {
      resolve({
        data,
        total,
        total_pages,
      });
      return;
    });
  },
};
