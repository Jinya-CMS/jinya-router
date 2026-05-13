import type {ReactNode} from 'react';
import clsx from 'clsx';
import Heading from '@theme/Heading';
import styles from './styles.module.css';

type FeatureItem = {
  title: string;
  description: ReactNode;
};

const FeatureList: FeatureItem[] = [
  {
    title: 'Attribute-based Routing',
    description: (
      <>
        Define your routes directly in your controllers using PHP attributes.
        Keep your routing logic close to your implementation.
      </>
    ),
  },
  {
    title: 'PSR-15 Middleware',
    description: (
      <>
        Full support for PSR-15 middleware. Easily add authentication,
        logging, and other cross-cutting concerns to your application.
      </>
    ),
  },
  {
    title: 'Fast and Efficient',
    description: (
      <>
        Built on top of fast-route for high-performance routing
        and uses battle-tested components from the Laminas project.
      </>
    ),
  },
];

function Feature({title, description}: FeatureItem) {
  return (
    <div className={clsx('col col--4')}>
      <div className="text--center padding-horiz--md">
        <Heading as="h3">{title}</Heading>
        <p>{description}</p>
      </div>
    </div>
  );
}

export default function HomepageFeatures(): ReactNode {
  return (
    <section className={styles.features}>
      <div className="container">
        <div className="row">
          {FeatureList.map((props, idx) => (
            <Feature key={idx} {...props} />
          ))}
        </div>
      </div>
    </section>
  );
}
