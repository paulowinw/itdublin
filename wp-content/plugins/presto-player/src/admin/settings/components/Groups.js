const { Card, CardBody, CardFooter, Modal, Button } = wp.components;
import SaveButton from "./SaveButton";
import Fields from "./Fields";
import Group from "./Group";

export default ({ groups }) => {
  const showDialog = (group) => {
    return (
      group?.disabled?.title &&
      group?.disabled?.message &&
      group?.disabled?.link
    );
  };

  return (
    <>
      {Object.keys(groups).length &&
        Object.keys(groups).map((key) => {
          if (!Object.keys(groups[key]?.fields || {}).length) {
            return;
          }
          if (groups[key]?.hidden) {
            return;
          }
          const group = groups[key];
          return <Group key={key} group={group} name={key} />;
        })}
    </>
  );
};
