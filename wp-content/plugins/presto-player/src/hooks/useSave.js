import { __ } from "@wordpress/i18n";
import { select, useDispatch } from "@wordpress/data";
import { store as coreStore } from "@wordpress/core-data";

export default function useSave() {
  const { saveEditedEntityRecord } = useDispatch(coreStore);

  /**
   * Handle the form submission
   */
  const save = async () => {
    // build up pending records to save.
    const dirtyRecords = select(
      coreStore
    ).__experimentalGetDirtyEntityRecords();
    const pendingSavedRecords = [];

    dirtyRecords.forEach(({ kind, name, key }) => {
      pendingSavedRecords.push(
        saveEditedEntityRecord(kind, name, key, {
          throwOnError: true,
        })
      );
    });

    // check values.
    const values = await Promise.all(pendingSavedRecords);
    if (values.some((value) => typeof value === "undefined")) {
      throw { message: "Saving failed." };
    }

    return true;
  };

  return {
    save,
  };
}
