import { Toolbar, Button } from "@wordpress/components";
import { check } from "@wordpress/icons";
import { __ } from "@wordpress/i18n";
import EditContext from "../blocks/reusable-display/context";
import { useContext } from "@wordpress/element";

export default () => {
  const { isEditing, setIsEditing } = useContext(EditContext);
  return (
    isEditing && (
      <Toolbar>
        <Button icon={check} onClick={() => setIsEditing(false)}>
          {__("Done Editing", "presto-player")}
        </Button>
      </Toolbar>
    )
  );
};
