const { Card, CardBody } = wp.components;
import Loading from "@/admin/settings/components/Loading";

export default ({ loading, title, value, label }) => {
  if (loading) {
    return (
      <Card>
        <CardBody>
          <Loading />
        </CardBody>
      </Card>
    );
  }

  return (
    <Card className="presto-player__stat-card">
      <CardBody>
        <div className="presto-subtitle">{title}</div>
        <h1>{value}</h1>
        <div>{label}</div>
      </CardBody>
    </Card>
  );
};
