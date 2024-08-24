// import apiFetch from "@wordpress/api-fetch";
const { apiFetch } = wp;

// nonce and root
apiFetch.use(apiFetch.createNonceMiddleware(prestoPlayer.nonce));
apiFetch.use(
  apiFetch.createRootURLMiddleware(
    prestoPlayer.root + prestoPlayer.prestoVersionString
  )
);

export default apiFetch;
