/**
 * WordPress dependencies
 */
import { useSelect } from "@wordpress/data";
import { __ } from "@wordpress/i18n";
import { store as coreStore } from "@wordpress/core-data";
import { Button } from "@wordpress/components";

export default function SaveButton({
  onSave,
  children,
  busy,
  loading,
  ...rest
}) {
  const { isDirty, isSaving } = useSelect((select) => {
    const {
      __experimentalGetDirtyEntityRecords,
      isSavingEntityRecord,
    } = select(coreStore);

    const dirtyEntityRecords = (
      __experimentalGetDirtyEntityRecords() || []
    ).filter((r) => r.name !== "webhook");

    return {
      isDirty: dirtyEntityRecords.length > 0,
      isSaving: dirtyEntityRecords.some((record) =>
        isSavingEntityRecord(record.kind, record.name, record.key)
      ),
    };
  }, []);

  const disabled = !isDirty || isSaving;

  if (loading) {
    return (
      <ScSkeleton
        style={{
          width: "120px",
          height: "35px",
          display: "inline-block",
        }}
      ></ScSkeleton>
    );
  }

  return (
    <Button
      isPrimary
      aria-disabled={disabled}
      disabled={disabled || isSaving || busy}
      isBusy={isSaving || busy}
      onClick={disabled ? undefined : onSave}
      {...rest}
    >
      {children}
    </Button>
  );
}
