import apiFetch from "@wordpress/api-fetch";

// nonce and root
apiFetch.use(apiFetch.createNonceMiddleware(prestoPlayer.nonce));
apiFetch.use(
  apiFetch.createRootURLMiddleware(
    prestoPlayer.root + prestoPlayer.wpVersionString
  )
);

export default apiFetch;
