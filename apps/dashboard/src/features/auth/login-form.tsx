"use client"

import Image from "next/image"
import Link from "next/link"
import { useRouter, useSearchParams } from "next/navigation"
import { Controller, useForm, type Control } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"
import { Icon } from "@/components/icon"
import { Button } from "@/components/ui/button"
import { Field, FieldError, FieldLabel } from "@/components/ui/field"
import { InputGroup, InputGroupAddon, InputGroupInput } from "@/components/ui/input-group"
import { Switch } from "@/components/ui/switch"
import { Notice } from "@/components/shared/notice"
import { useLogin } from "./queries"

const loginSchema = z.object({
  email: z.email("Indiquez une adresse e-mail valide"),
  password: z.string().min(1, "Indiquez votre mot de passe"),
  remember: z.boolean(),
})

type LoginValues = z.infer<typeof loginSchema>

export function LoginForm() {
  const router = useRouter()
  const next = useSearchParams().get("next") ?? "/"
  const login = useLogin()
  const { control, handleSubmit } = useForm<LoginValues>({
    resolver: zodResolver(loginSchema),
    defaultValues: { email: "", password: "", remember: true },
  })

  const onSubmit = handleSubmit((values) => login.mutate(values, { onSuccess: () => router.replace(next) }))

  return (
    <form onSubmit={onSubmit} noValidate className="flex w-full max-w-100 flex-col gap-5">
      <Image src="/brand/pigfarm-logo-horizontal.png" alt="Pig Farm" width={91} height={32} />
      <div className="flex flex-col gap-1">
        <h1 className="text-2xl font-bold">Connexion</h1>
        <p className="text-body text-muted-foreground">Accédez au tableau de bord de vos fermes.</p>
      </div>

      {login.isError && (
        <Notice tone="danger" title="Connexion impossible">
          {login.error.message}
        </Notice>
      )}

      <IconInput control={control} name="email" label="Adresse e-mail" type="email" icon="envelope" autoComplete="email" />
      <IconInput control={control} name="password" label="Mot de passe" type="password" icon="lock" autoComplete="current-password" />

      <div className="flex flex-wrap items-center justify-between gap-3">
        <Controller
          control={control}
          name="remember"
          render={({ field }) => (
            <label className="flex cursor-pointer items-center gap-3 text-body font-semibold">
              <Switch checked={field.value} onCheckedChange={field.onChange} />
              Rester connecté
            </label>
          )}
        />
        {/* TODO(#1.1): forgot-password flow */}
        <Link href="/forgot-password" className="text-sm text-primary underline underline-offset-3 hover:text-primary-hover">
          Mot de passe oublié ?
        </Link>
      </div>

      <Button type="submit" size="lg" loading={login.isPending}>
        Se connecter
      </Button>
      {/* TODO(#1.2): Google OAuth (Socialite) */}
      <Button type="button" variant="outline" size="lg" disabled>
        Continuer avec Google
      </Button>
    </form>
  )
}

type IconInputProps = {
  control: Control<LoginValues>
  name: "email" | "password"
  label: string
  type: "email" | "password"
  icon: string
  autoComplete: string
}

/** Required text field with a leading icon, wired to react-hook-form. */
function IconInput({ control, name, label, type, icon, autoComplete }: IconInputProps) {
  return (
    <Controller
      control={control}
      name={name}
      render={({ field, fieldState }) => (
        <Field data-invalid={fieldState.invalid}>
          <FieldLabel htmlFor={name}>
            {label}
            <span aria-hidden className="text-destructive">
              *
            </span>
          </FieldLabel>
          <InputGroup>
            <InputGroupAddon>
              <Icon name={icon} />
            </InputGroupAddon>
            <InputGroupInput {...field} id={name} type={type} autoComplete={autoComplete} aria-invalid={fieldState.invalid} />
          </InputGroup>
          {fieldState.error && <FieldError>{fieldState.error.message}</FieldError>}
        </Field>
      )}
    />
  )
}
