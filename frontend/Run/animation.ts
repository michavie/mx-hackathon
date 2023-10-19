import { Variants } from 'framer-motion'

export const Animation = {
  variants: {
    runPreview: {
      hidden: { opacity: 0, y: 50, scale: 0.3 },
      show: {
        opacity: 1,
        y: 0,
        scale: 1,
        transition: { duration: 0.2 },
      },
      exit: { opacity: 0, scale: 0.5, transition: { duration: 0.2 } },
    } as Variants,
  },
}
