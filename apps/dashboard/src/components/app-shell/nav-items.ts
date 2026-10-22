export type NavItem = { href: string; label: string; icon: string; ownerOnly?: boolean }

export const NAV_ITEMS: NavItem[] = [
  { href: "/", label: "Aujourd’hui", icon: "house" },
  { href: "/animals", label: "Animaux", icon: "layer-group" },
  { href: "/reproduction", label: "Reproduction", icon: "venus-mars" },
  { href: "/feed", label: "Alimentation", icon: "wheat-awn" },
  { href: "/health", label: "Santé et stocks", icon: "syringe" },
  { href: "/finance", label: "Argent", icon: "sack-dollar", ownerOnly: true },
]

export const isActive = (pathname: string, href: string) => (href === "/" ? pathname === "/" : pathname.startsWith(href))
