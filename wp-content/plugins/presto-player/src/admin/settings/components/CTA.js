const { Button, Card, CardBody } = wp.components;
const { dispatch } = wp.data;

export default ({ option, value, optionName, className }) => {
  return (
    <Card isBorderless className={className}>
      <CardBody isShady>
        {!!option?.name && <h2>{option.name}</h2>}
        {!!option.help && <p>{option.help}</p>}
        {!!option?.button?.text && (
          <Button
            isPrimary
            target={option?.button?.target}
            href={option?.button?.link}
          >
            {option.button.text}
          </Button>
        )}
      </CardBody>
    </Card>
  );
};
