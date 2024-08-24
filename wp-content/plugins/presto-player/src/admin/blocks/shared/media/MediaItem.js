export default ({ item, onClick, className }) => {
  function bytesToSize(bytes) {
    var sizes = ["Bytes", "KB", "MB", "GB", "TB"];
    if (bytes == 0) return "0 Byte";
    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return Math.round(bytes / Math.pow(1024, i), 2) + " " + sizes[i];
  }

  function formatDate(date) {
    return new Date(date).toLocaleString();
  }

  const isEncoding = () => {
    return !!item?.encodeProgress && item.encodeProgress !== 100;
  };

  return (
    <div
      className={`presto-player__media-list-item ${className}`}
      onClick={onClick}
    >
      <div className="presto-player__media-list-item-icon">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          strokeWidth="2"
          strokeLinecap="round"
          strokeLinecap="round"
        >
          <polygon points="23 7 16 12 23 17 23 7"></polygon>
          <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
        </svg>
      </div>
      <div className="presto-player__media-list-item-title">{item?.title}</div>
      {isEncoding() && (
        <div className="presto-player__media-list-item-size">Encoding...</div>
      )}
      {!isEncoding() && (
        <div className="presto-player__media-list-item-size">
          {bytesToSize(item?.size)}
        </div>
      )}
      {!isEncoding() && (
        <div className="presto-player__media-list-item-modified">
          {formatDate(item.updated_at)}
        </div>
      )}
    </div>
  );
};
