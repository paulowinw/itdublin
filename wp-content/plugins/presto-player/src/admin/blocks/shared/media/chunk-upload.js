/**
 * Upload files in chunks
 * Based on: https://github.com/deliciousbrains/wp-dbi-file-uploader
 */
export default function ({ file, path, onProgress, onComplete, onError }) {
  let reader = {};
  let chunk = 0;
  let cancelled = false;

  // get max upload size, max out at 16mb chunks
  const max_request_size = Math.min(
    prestoPlayerAdmin.wp_max_upload_size - 1000000,
    15900000
  );
  let slice_size = Math.max(max_request_size, 1900000); // ~2MB fallback as minimum
  slice_size = Math.min(slice_size, 104857600); // make the max size 100MB

  // export percent done
  let percent_done;

  // chunk upload
  const chunkUpload = function () {
    chunk = 0;

    reader = new FileReader();
    upload_file(0);
    return this;
  };

  // upload file
  const upload_file = async (start) => {
    const next_slice = start + slice_size + 1;
    const blob = file.slice(start, next_slice);
    const chunks = Math.ceil(file.size / (slice_size + 1));
    chunk++;
    onProgress((chunk / chunks) * 100);

    const body = new FormData();
    body.append("file", blob);
    body.append("name", file.name);
    body.append("chunk", chunk);
    body.append("chunks", chunks);

    try {
      const file_url = await wp.apiFetch({
        path,
        method: "POST",
        body,
      });

      if (cancelled) {
        console.log("cancelled");
        return;
      }

      if (next_slice < file.size) {
        onProgress(percent_done);
        upload_file(next_slice);
      } else {
        onComplete(file_url);
      }
    } catch (e) {
      onError(e);
      console.error(e);
    }
  };

  chunkUpload();

  return {
    cancel: () => {
      cancelled = true;
    },
  };
}
