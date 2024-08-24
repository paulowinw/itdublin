import { SnackbarList } from "@wordpress/components";
import { useDispatch, useSelect } from "@wordpress/data";

import { store as noticesStore } from "@wordpress/notices";

export default ({ className }) => {
  const notices = useSelect((select) => select(noticesStore).getNotices());
  const { removeNotice } = useDispatch(noticesStore);
  const snackbarNotices = notices.filter(({ type }) => type === "snackbar");

  return (
    <SnackbarList
      notices={snackbarNotices}
      className={className}
      onRemove={removeNotice}
    />
  );
};
