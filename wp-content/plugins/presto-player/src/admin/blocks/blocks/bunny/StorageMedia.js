const { __ } = wp.i18n;
import chunkUpload from "@/admin/blocks/shared/media/chunk-upload";
import MediaPopup from "@/admin/blocks/shared/media/MediaPopup";
const { useState, useEffect } = wp.element;

export default ({ closePopup, noticeOperations, onSelect, isPrivate }) => {
  const [videos, setVideos] = useState([]);
  const [fetching, setFetching] = useState(false);
  const [progress, setProgress] = useState(0);
  const [progressMessage, setProgressMessage] = useState("");
  const [error, setError] = useState("");

  const endpoint = isPrivate ? "private-videos" : "public-videos";

  // reset progress and error on open
  useEffect(() => {
    setProgress(0);
    setError("");
  }, []);

  // handle error
  const onError = (message) => {
    noticeOperations.removeAllNotices();
    noticeOperations.createErrorNotice(message);
  };

  const uploadFile = async (files) => {
    setError("");
    setFetching(true);
    setProgressMessage(__("Uploading", "presto-player"));
    chunkUpload({
      file: files[0],
      path: `presto-player/v1/bunny/upload`,
      onProgress: (percent) => {
        setProgress(percent - 10); // leave 10% for storing
      },
      onComplete: storeFile,
      onError: (e) => {
        setError(e.message);
        setProgress(0);
        setFetching(false);
      },
    });
  };

  const storeFile = async ({ path, name }) => {
    setProgressMessage(__("Storing", "presto-player"));
    try {
      const videos = await wp.apiFetch({
        path: `presto-player/v1/bunny/${endpoint}`,
        method: "POST",
        data: {
          path,
          name,
        },
      });
      setVideos(videos);
    } catch (e) {
      setError(e.message);
      console.error(e);
    } finally {
      setProgress(0);
      setFetching(false);
    }
  };

  const deleteVideo = async (video) => {
    try {
      setVideos((videos || []).filter((item) => item !== video));
      await wp.apiFetch({
        path: `presto-player/v1/bunny/${endpoint}`,
        method: "DELETE",
        data: {
          name: video?.title,
        },
      });
    } catch (e) {
      setError(e.message);
    }
  };

  // loads videos for media popup
  const loadVideos = async () => {
    try {
      setFetching(true);
      const videos = await wp.apiFetch({
        path: `presto-player/v1/bunny/${endpoint}`,
      });
      setVideos(videos);
    } catch (e) {
      setError(e.message);
    } finally {
      setFetching(false);
    }
  };

  return (
    <MediaPopup
      onClose={closePopup}
      progressMessage={progressMessage}
      fetching={fetching}
      error={error}
      onUpload={uploadFile}
      onLoad={loadVideos}
      items={videos}
      progress={progress}
      onDelete={deleteVideo}
      onSelect={onSelect}
      onError={onError}
      header={
        isPrivate
          ? __("Bunny.net Private Video Storage", "presto-player")
          : __("Bunny.net Public Video Storage", "presto-player")
      }
      title={
        isPrivate
          ? __("Private Video Library", "presto-player")
          : __("Public Video Library", "presto-player")
      }
    />
  );
};
